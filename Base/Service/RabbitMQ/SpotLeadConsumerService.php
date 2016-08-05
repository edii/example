<?php

namespace Araneum\Base\Service\RabbitMQ;

use Araneum\Base\Service\ApiSenders\SpotApiSenderService;
use Araneum\Bundle\MailBundle\Entity\MailEvent as ApiMailEvent;
use Araneum\Bundle\AgentBundle\Entity\Lead;
use Araneum\Bundle\MailBundle\Event\MailEvent;
use Araneum\Bundle\MailBundle\MailEvents;
use Guzzle\Http\Exception\RequestException;
use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Class SpotLeadConsumerService
 *
 * @package Araneum\Base\Service\RabbitMQ
 */
class SpotLeadConsumerService extends BaseConsumerService implements ConsumerInterface
{
    /**
     * @var SpotApiSenderService
     */
    private $spotApiSenderService;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var boolean
     */
    protected $leadMailSending;

    /**
     * SpotLeadConsumerService constructor.
     *
     * @param SpotApiSenderService     $spotApiSenderService
     * @param ProducerService          $producer
     * @param MessageConversionHelper  $msgConvertHelper
     * @param EntityManager            $entityManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param Producer                 $deadMessagesProducer
     * @param boolean                  $leadMailSending
     */
    public function __construct(
        SpotApiSenderService $spotApiSenderService,
        ProducerService $producer,
        MessageConversionHelper $msgConvertHelper,
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher,
        Producer $deadMessagesProducer,
        $leadMailSending
    ) {
        parent::__construct($producer, $msgConvertHelper, $deadMessagesProducer);
        $this->spotApiSenderService = $spotApiSenderService;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->leadMailSending = $leadMailSending;
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
        $response = $this->spotApiSenderService->send((array) $data->data, (array) $data->credential);
        $success = $this->messageIteration($data, $response);
        if ($success == BaseConsumerService::RESPONSE_SUCCESS) {
            $errors = $this->spotApiSenderService->getErrors($response);
            if ($errors !== null) {
                $this->deadMessagesProducer->publish(
                    $this->createDataForDeadMessagesQueue($errors),
                    self::DEAD_MSG_ROUTING_KEY
                );

                return self::DEAD_MSG_ROUTING_KEY;
            }

            $responseBody = $response->getBody(true);
            $decodedSpotResponse = json_decode($responseBody, true);
            $leadFields['spotId'] = !empty($decodedSpotResponse['status']['Lead']['data_id']) ? $decodedSpotResponse['status']['Lead']['data_id'] : null;
            $this->updateLead($log, $leadFields);
        }
    }

    /**
     * Update lead spotId and creates an event for send axMail
     *
     * @param array $log
     * @param array $fields
     * @throws \Doctrine\ORM\ORMException
     */
    private function updateLead(array $log, array $fields)
    {
        $lead = $this->entityManager->getRepository("AraneumAgentBundle:Lead")->findOneById($log['leadId']);

        $lead->setSpotId($fields['spotId']);

        $this->entityManager->persist($lead);
        $this->entityManager->flush();

        if ($this->leadMailSending) {
            $this->createApiMail($lead, MailEvents::AXMAIL_NEW);
        }
    }

    /**
     * Create and dispatch Lead event
     *
     * @param Lead   $lead
     * @param string $eventName
     */
    private function createApiMail(Lead $lead, $eventName)
    {
        $apiMailEvent = (new ApiMailEvent())
            ->setApplication($lead->getApplication())
            ->setTemplate(ApiMailEvent::AXMAIL_EVENT_APROVEMENT)
            ->setData(
                [
                    'user_id' => $lead->getSpotId(),
                    'email' => $lead->getEmail(),
                    'first_name' => $lead->getFirstName(),
                    'last_name' => $lead->getLastName(),
                ]
            )
        ;

        $this->entityManager->persist($apiMailEvent);
        $this->entityManager->flush();

        $event = new MailEvent();
        $event->setMail($apiMailEvent);

        $this->eventDispatcher->dispatch($eventName, $event);
    }
}
