<?php

namespace Araneum\Base\Service\RabbitMQ;

use Araneum\Base\Service\ApiSenders\SpotApiSenderService;
use Araneum\Bundle\MailBundle\Entity\Mail;
use Araneum\Bundle\MailBundle\Entity\MailLog;
use Araneum\Bundle\MailBundle\Service\MailsSenderService;
use Doctrine\ORM\EntityManager;
use Guzzle\Service;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class MailsConsumerService
 *
 * @package Araneum\Base\Service\RabbitMQ
 */
class MailsConsumerService implements ConsumerInterface
{
    const TEXT_SENT     = 'Mail was sent';
    const TEXT_NOT_SENT = 'Error while sending mail';
    const TEXT_ERROR    = 'Message does not contain Mail object';

    /** @var  MailsSenderService */
    protected $mailsSenderService;

    /** @var  SerializerInterface */
    protected $serializer;

    /** @var  EntityManager */
    protected $entityManager;

    /** @var  MessageConversionHelper */
    protected $msgConvertHelper;

    /**
     * MailsConsumerService constructor.
     *
     * @param MailsSenderService      $mailsSenderService
     * @param MessageConversionHelper $msgConvertHelper
     * @param SerializerInterface     $serializer
     * @param EntityManager           $entityManager
     */
    public function __construct(
        MailsSenderService $mailsSenderService,
        MessageConversionHelper $msgConvertHelper,
        SerializerInterface $serializer,
        EntityManager $entityManager
    ) {
        $this->mailsSenderService = $mailsSenderService;
        $this->msgConvertHelper = $msgConvertHelper;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    /**
     * Receive message
     *
     * @param AMQPMessage $message
     * @return bool
     */
    public function execute(AMQPMessage $message)
    {
        $data = $this->msgConvertHelper->decodeMsg($message->body);
        $mail = $this->deserializeMail($data->data);
        if (is_object($mail) && $mail instanceof Mail) {
            $isSent = $this->mailsSenderService->sendMail($mail);
            echo $isSent ? self::TEXT_SENT : self::TEXT_NOT_SENT;
        } else {
            echo self::TEXT_ERROR;
        }
    }

    /**
     * Deserialize message body as Mail entity
     *
     * @param string $serializedMail
     * @return array|\JMS\Serializer\scalar|object
     */
    private function deserializeMail($serializedMail)
    {
        return $this->serializer->deserialize(
            $serializedMail,
            Mail::class,
            'json',
            DeserializationContext::create()->setGroups(['rabbitMQ'])
        );
    }
}
