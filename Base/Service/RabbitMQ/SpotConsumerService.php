<?php

namespace Araneum\Base\Service\RabbitMQ;

use Araneum\Base\Service\ApiSenders\SpotApiSenderService;
use Guzzle\Service;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class SpotConsumerService
 *
 * @package Araneum\Base\Service\RabbitMQ
 */
class SpotConsumerService extends BaseConsumerService implements ConsumerInterface
{
    const TEXT_RESPONSE = 'Response: ';
    const TEXT_ERROR = 'Error';

    /**
     * @var SpotApiSenderService
     */
    private $spotApiSenderService;

    /**
     * Consumer constructor.
     *
     * @param SpotApiSenderService    $spotApiSenderService
     * @param ProducerService         $producer
     * @param MessageConversionHelper $msgConvertHelper
     * @param Producer                $deadMessagesProducer
     */
    public function __construct(
        SpotApiSenderService $spotApiSenderService,
        ProducerService $producer,
        MessageConversionHelper $msgConvertHelper,
        Producer $deadMessagesProducer
    ) {
        parent::__construct($producer, $msgConvertHelper, $deadMessagesProducer);
        $this->spotApiSenderService = $spotApiSenderService;
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
        $spotResponse = $this->spotApiSenderService->send((array) $data->data, (array) $data->credential);
        $success = $this->messageIteration($data, $spotResponse);
        if ($success == BaseConsumerService::RESPONSE_SUCCESS) {
            echo self::TEXT_RESPONSE.$spotResponse->getBody(true).PHP_EOL;
        } else {
            echo self::TEXT_ERROR;
        }
    }
}
