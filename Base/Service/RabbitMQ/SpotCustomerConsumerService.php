<?php

namespace Araneum\Base\Service\RabbitMQ;

use Araneum\Base\Service\ApiSenders\SpotApiSenderService;
use Araneum\Bundle\AgentBundle\AgentEvents;
use Araneum\Bundle\AgentBundle\Entity\Customer;
use Araneum\Bundle\AgentBundle\Entity\CustomerLog;
use Araneum\Bundle\AgentBundle\Event\CustomerEvent;
use Guzzle\Http\Exception\RequestException;
use Doctrine\ORM\EntityManager;
use Guzzle\Service;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Class SpotCustomerConsumerService
 *
 * @package Araneum\Base\Service\RabbitMQ
 */
class SpotCustomerConsumerService extends BaseConsumerService implements ConsumerInterface
{
    /**
     * @var EntityManager
     */
    protected $em;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var SpotApiSenderService
     */
    private $spotApiSenderService;

    /**
     * Consumer constructor.
     *
     * @param SpotApiSenderService     $spotApiSenderService
     * @param ProducerService          $producer
     * @param MessageConversionHelper  $msgConvertHelper
     * @param EntityManager            $em
     * @param EventDispatcherInterface $dispatcher
     * @param Producer                 $deadMessagesProducer
     */
    public function __construct(
        SpotApiSenderService $spotApiSenderService,
        ProducerService $producer,
        MessageConversionHelper $msgConvertHelper,
        EntityManager $em,
        EventDispatcherInterface $dispatcher,
        Producer $deadMessagesProducer
    ) {
        parent::__construct($producer, $msgConvertHelper, $deadMessagesProducer);
        $this->spotApiSenderService = $spotApiSenderService;
        $this->em = $em;
        $this->dispatcher = $dispatcher;
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
        $log = (array) $data->credential->log;
        $spotResponse = $this->spotApiSenderService->send((array) $data->data, (array) $data->credential);
        $success = $this->messageIteration($data, $spotResponse);
        if ($success == BaseConsumerService::RESPONSE_SUCCESS) {
            $errors = $this->spotApiSenderService->getErrors($spotResponse);
            if ($errors !== null) {
                $this->deadMessagesProducer->publish(
                    $this->createDataForDeadMessagesQueue($errors),
                    self::DEAD_MSG_ROUTING_KEY
                );

                return self::DEAD_MSG_ROUTING_KEY;
            }

            $responseBody = $spotResponse->getBody(true);
            $decodedSpotResponse = json_decode($responseBody, true);
            $customerFields['spotId'] = !empty($decodedSpotResponse['status']['Customer']['data_id'])
                ? $decodedSpotResponse['status']['Customer']['data_id'] : null;
            $customerFields['password'] = !empty($data->data->password) ? $data->data->password : null;
            $this->updateCustomer($log, $customerFields);
            $this->createCustomerLog($log, $responseBody, CustomerLog::STATUS_OK);
        } else {
            $this->createCustomerLog($log, $spotResponse->getMessage(), CustomerLog::STATUS_ERROR);
        }
    }

    /**
     * Create and save customer log
     *
     * @param array  $log
     * @param string $logMessage
     * @param int    $status
     * @throws \Doctrine\ORM\ORMException
     */
    private function createCustomerLog(array $log, $logMessage, $status)
    {
        $customerLog = (new CustomerLog())
            ->setAction($log['action'])
            ->setApplication($this->em->getReference('AraneumMainBundle:Application', $log['applicationId']))
            ->setCustomer($this->em->getReference('AraneumAgentBundle:Customer', $log['customerId']))
            ->setResponse($logMessage)
            ->setStatus($status);

        $this->em->persist($customerLog);
        $this->em->flush();
    }

    /**
     * Update customer $deliveredAt and $spotId
     *
     * @param array $log
     * @param array $fields
     * @throws \Doctrine\ORM\ORMException
     */
    private function updateCustomer(array $log, array $fields)
    {
        if ($log['action'] == CustomerLog::ACTION_CREATE) {
            $customer = $this->em->getRepository("AraneumAgentBundle:Customer")->findOneById($log['customerId']);
            $customer->setDeliveredAt(new \DateTime());
            $customer->setSpotId($fields['spotId']);
            $this->em->persist($customer);
            $this->em->flush();

            $customer->setPassword($fields['password']);
            $this->createCustomerEvent($customer, AgentEvents::CUSTOMER_LOGIN, (array) $log['headers']);
        }
    }

    /**
     * Create and dispatch Customer event
     *
     * @param Customer $customer
     * @param string   $eventName
     * @param array    $headers
     */
    private function createCustomerEvent(Customer $customer, $eventName, $headers)
    {
        $event = (new CustomerEvent())
            ->setCustomer($customer)
            ->setHeaders($headers)
        ;

        $this->dispatcher->dispatch($eventName, $event);
    }
}
