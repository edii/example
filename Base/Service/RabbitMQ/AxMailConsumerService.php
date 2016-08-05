<?php

namespace Araneum\Base\Service\RabbitMQ;

use Araneum\Base\Service\ApiSenders\AxMailApiSenderService;
use Araneum\Bundle\MailBundle\Entity\MailEvent;
use Doctrine\ORM\EntityManager;
use Guzzle\Service;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Class AxMailConsumerService
 *
 * @package Araneum\Base\Service\RabbitMQ
 */
class AxMailConsumerService extends BaseConsumerService implements ConsumerInterface
{
    const TEXT_RESPONSE = 'Response: ';
    const TEXT_ERROR = 'Error';

    /**
     * @var AxMailApiSenderService
     */
    private $axMailApiSenderService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Consumer constructor.
     *
     * @param AxMailApiSenderService  $axMailApiSenderService
     * @param ProducerService         $producer
     * @param MessageConversionHelper $msgConvertHelper
     * @param Producer                $deadMessagesProducer
     * @param EntityManager           $entityManager
     */
    public function __construct(
        AxMailApiSenderService  $axMailApiSenderService,
        ProducerService $producer,
        MessageConversionHelper $msgConvertHelper,
        Producer $deadMessagesProducer,
        EntityManager $entityManager
    ) {
        parent::__construct($producer, $msgConvertHelper, $deadMessagesProducer);
        $this->axMailApiSenderService = $axMailApiSenderService;
        $this->entityManager = $entityManager;
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
        $mailId = $data->credential->log->axMailId;
        $axMailResponse = $this->axMailApiSenderService->send((array) $data->data, (array) $data->credential);
        $success = $this->messageIteration($data, $axMailResponse);

        if ($success == BaseConsumerService::RESPONSE_SUCCESS) {
            $this->changeAxMailStatus($mailId, MailEvent::STATUS_SEND);
            echo self::TEXT_RESPONSE.$axMailResponse->getBody(true).PHP_EOL;
        } else {
            $this->changeAxMailStatus($mailId, MailEvent::STATUS_FAILED);
            echo self::TEXT_ERROR;
        }
    }

    private function changeAxMailStatus($id, $status)
    {
        $axMail = $this->entityManager->getRepository('AraneumMailBundle:MailEvent')->find($id);
        $axMail->setStatus($status);
        $this->entityManager->persist($axMail);
        $this->entityManager->flush();
    }
}
