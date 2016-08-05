<?php

namespace Araneum\Base\Service\RabbitMQ;

use Araneum\Base\Service\ApiSenders\ApplicationApiSenderService;
use Araneum\Base\Service\ApiSenders\SpotApiSenderService;
use Araneum\Bundle\AgentBundle\Entity\Agent;
use Araneum\Bundle\AgentBundle\Entity\Customer;
use Araneum\Bundle\AgentBundle\Entity\CustomerLog;
use Araneum\Bundle\MainBundle\Entity\Application;
use Doctrine\ORM\EntityManager;
use Guzzle\Http\Exception\RequestException;
use Guzzle\Service;
use JMS\Serializer\SerializerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\HttpFoundation\Request;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Class SpotCustomerLoginConsumerService
 *
 * @package Araneum\Base\Service\RabbitMQ
 */
class SpotCustomerLoginConsumerService extends BaseConsumerService implements ConsumerInterface
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @var SpotApiSenderService
     */
    private $spotApiSenderService;
    /**
     * @var ApplicationApiSenderService
     */
    private $applicationApiSenderService;

    /**
     * SpotCustomerLoginConsumerService constructor.
     *
     * @param SpotApiSenderService        $spotApiSenderService
     * @param ProducerService             $producer
     * @param MessageConversionHelper     $msgConvertHelper
     * @param EntityManager               $em
     * @param SerializerInterface         $serializer
     * @param ApplicationApiSenderService $applicationApiSenderService
     * @param Producer                    $deadMessagesProducer
     */
    public function __construct(
        SpotApiSenderService $spotApiSenderService,
        ProducerService $producer,
        MessageConversionHelper $msgConvertHelper,
        EntityManager $em,
        SerializerInterface $serializer,
        ApplicationApiSenderService $applicationApiSenderService,
        Producer $deadMessagesProducer
    ) {
        parent::__construct($producer, $msgConvertHelper, $deadMessagesProducer);
        $this->spotApiSenderService = $spotApiSenderService;
        $this->applicationApiSenderService = $applicationApiSenderService;
        $this->em = $em;
        $this->serializer = $serializer;
    }

    /**
     * Receive message
     *
     * @param AMQPMessage $message
     * @return string
     */
    public function execute(AMQPMessage $message)
    {
        $data = $this->msgConvertHelper->decodeMsg($message->body);
        /** @var Customer $customer */
        $customer = $this->serializer->deserialize($data->data, 'Araneum\Bundle\AgentBundle\Entity\Customer', 'json');

        $customerObject = $this->em->getRepository('AraneumAgentBundle:Customer')->find($customer->getId());
        $spotPublicUrl = $customerObject->getApplication()->getAgentByType(Agent::SPOTOPTION)->getPublicUrl();

        $headers = [];
        if (!empty($customer->getUserAgent())) {
            $headers['User-Agent'] = $customer->getUserAgent();
        }

        if (!empty($customer->getIp())) {
            $headers['X-Forwarded-For'] = $customer->getIp();
        }

        $log = [
            'applicationId' => $customerObject->getApplication()->getId(),
            'agentId' => $customerObject->getApplication()->getAgentByType(Agent::SPOTOPTION)->getId(),

        ];

        $generatedSpotSession = $this->spotApiSenderService->generateSpotSession();

        $spotResponse = $this->spotApiSenderService->sendToPublicUrl(
            Request::METHOD_POST,
            $spotPublicUrl,
            $headers,
            [
                'email' => $customer->getEmail(),
                'password' => $customer->getPassword(),
                'spotsession' => $generatedSpotSession,
            ],
            $log
        );

        $success = $this->messageIteration($data, $spotResponse);

        if ($success == BaseConsumerService::RESPONSE_SUCCESS) {
            try {
                if ($this->spotApiSenderService->getErrorsFromPublic($spotResponse) !== null) {
                    echo 'ERROR: '.$this->spotApiSenderService->getErrorsFromPublic($spotResponse).PHP_EOL;
                    throw new RequestException($this->spotApiSenderService->getErrorsFromPublic($spotResponse));
                }

                $decodedResponse = $spotResponse->json();
                $spotCustomerData = [
                    'customerId' => $decodedResponse['customerId'],
                    'spotsession' => $generatedSpotSession,
                ];

                $applicationCredentials = [
                    'credentials' => $this->createCredentialsForApplicationApiOfCustomer($data, $customer),
                    'log' => $log,
                ];

                $this->applicationApiSenderService->send(
                    $spotCustomerData,
                    $applicationCredentials
                );

                $this->createCustomerLog($customer, $spotResponse->getBody(true), CustomerLog::STATUS_OK);

                $customerObject
                    ->setUserAgent($customer->getUserAgent())
                    ->setIp($customer->getIp());

                $this->em->persist($customerObject);
                $this->em->flush();
            } catch (\Exception $e) {
                $this->messageIteration($data, $e);

                $this->createCustomerLog($customer, $spotResponse->getBody(true), CustomerLog::STATUS_ERROR);
            }
        } else {
            $this->createCustomerLog($customer, $spotResponse->getMessage(), CustomerLog::STATUS_ERROR);
        }
    }

    /**
     * Create and save customer log
     *
     * @param Customer $customer
     * @param string   $logMessage
     * @param int      $status
     * @throws \Doctrine\ORM\ORMException
     */
    private function createCustomerLog(Customer $customer, $logMessage, $status)
    {
        $customerLog = (new CustomerLog())
            ->setAction(CustomerLog::ACTION_LOGIN)
            ->setApplication(
                $this->em->getReference('AraneumMainBundle:Application', $customer->getApplication()->getId())
            )
            ->setCustomer($this->em->getReference('AraneumAgentBundle:Customer', $customer->getId()))
            ->setResponse($logMessage)
            ->setStatus($status)
        ;

        $this->em->persist($customerLog);
        $this->em->flush();
    }

    /**
     * @param mixed       $data
     * @param Customer    $customer
     * @return array
     */
    private function createCredentialsForApplicationApiOfCustomer($data, $customer)
    {
        $credentials = (array) $data->credential;
        $log = (array) $credentials['log'];
        $headers = (array) $log['headers'] ?: [];
        $baseUrl = $headers['referer']?:$customer->getApplication()->getDomain();

        return [
            'baseUrl' => $baseUrl,
            'requestConfig' => ApplicationApiSenderService::SET_SPOT_USER_DATA,
            'urlParams' => [
                'siteId' => $customer->getSiteId(),
            ],
        ];
    }
}
