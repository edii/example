<?php

namespace Araneum\Base\Service\RabbitMQ;

use Araneum\Base\Service\ApiSenders\TransactApiSenderService;
use Guzzle\Service;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Class TransactConsumerService
 *
 * @package Araneum\Base\Service\RabbitMQ
 */
class TransactConsumerService extends BaseConsumerService implements ConsumerInterface
{
    const TEXT_RESPONSE = 'Response: ';
    const TEXT_ERROR = 'Error';

    /**
     * @var TransactApiSenderService
     */
    private $transactApiSenderService;

    /**
     * Consumer constructor.
     *
     * @param TransactApiSenderService $transactApiSenderService
     * @param ProducerService          $producer
     * @param MessageConversionHelper  $msgConvertHelper
     * @param Producer                 $deadMessagesProducer
     */
    public function __construct(
        TransactApiSenderService $transactApiSenderService,
        ProducerService $producer,
        MessageConversionHelper $msgConvertHelper,
        Producer $deadMessagesProducer
    ) {
        parent::__construct($producer, $msgConvertHelper, $deadMessagesProducer);
        $this->transactApiSenderService = $transactApiSenderService;
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
        $transactResponse = $this->transactApiSenderService->send(
            (array) $data->data,
            json_decode(json_encode($data->credential), true)
        );
        $success = $this->messageIteration($data, $transactResponse);

        if ($success == BaseConsumerService::RESPONSE_SUCCESS) {
            echo self::TEXT_RESPONSE.$transactResponse->getBody(true).PHP_EOL;
        } else {
            echo self::TEXT_ERROR;
        }
    }
}
