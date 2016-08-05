<?php

namespace Araneum\Base\Service\RabbitMQ;

use Araneum\Base\Service\ApiSenders\AweberApiSenderService;
use Guzzle\Service;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Class AweberConsumerService
 *
 * @package Araneum\Base\Service\RabbitMQ
 */
class AweberConsumerService extends BaseConsumerService implements ConsumerInterface
{
    const TEXT_RESPONSE = 'Response: ';
    const TEXT_ERROR = 'Error';

    /**
     * @var AweberApiSenderService
     */
    private $aweberApiSenderService;

    /**
     * Consumer constructor.
     *
     * @param AweberApiSenderService  $aweberApiSenderService
     * @param ProducerService         $producer
     * @param MessageConversionHelper $msgConvertHelper
     * @param Producer                $deadMessagesProducer
     */
    public function __construct(
        AweberApiSenderService  $aweberApiSenderService,
        ProducerService $producer,
        MessageConversionHelper $msgConvertHelper,
        Producer $deadMessagesProducer
    ) {
        parent::__construct($producer, $msgConvertHelper, $deadMessagesProducer);
        $this->aweberApiSenderService = $aweberApiSenderService;
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
        $aweberResponse = $this->aweberApiSenderService->send(
            (array) $data->data,
            json_decode(json_encode($data->credential), true)
        );
        $success = $this->messageIteration($data, $aweberResponse);

        if ($success == BaseConsumerService::RESPONSE_SUCCESS) {
            echo self::TEXT_RESPONSE.$aweberResponse->getBody(true).PHP_EOL;
        } else {
            echo self::TEXT_ERROR;
        }
    }
}
