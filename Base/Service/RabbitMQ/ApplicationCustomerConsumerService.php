<?php

namespace Araneum\Base\Service\RabbitMQ;

use Araneum\Base\Service\ApiSenders\ApplicationApiSenderService;
use Guzzle\Service;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class ApplicationCustomerConsumerService
 *
 * @package Araneum\Base\Service\RabbitMQ
 */
class ApplicationCustomerConsumerService extends BaseConsumerService implements ConsumerInterface
{
    const TEXT_RESPONSE = 'Response: ';
    const TEXT_ERROR    = 'Error';

    /** @var ApplicationApiSenderService */
    protected $applicationApiSenderService;

    /**
     * Consumer constructor.
     *
     * @param ApplicationApiSenderService $applicationApiSenderService
     * @param ProducerService             $producer
     * @param MessageConversionHelper     $msgConvertHelper
     * @param Producer                    $deadMessagesProducer
     */
    public function __construct(
        ApplicationApiSenderService $applicationApiSenderService,
        ProducerService $producer,
        MessageConversionHelper $msgConvertHelper,
        Producer $deadMessagesProducer
    ) {
        parent::__construct($producer, $msgConvertHelper, $deadMessagesProducer);
        $this->applicationApiSenderService = $applicationApiSenderService;
    }

    /**
     * Receive message
     *
     * @param  AMQPMessage $message
     * @return string
     */
    public function execute(AMQPMessage $message)
    {
        $data = $this->msgConvertHelper->decodeMsg($message->body);
        $helper = [
            'url' => $data->credential->url,
            'customerId' => $data->credential->customerId,
        ];

        $response = $this->applicationApiSenderService->send((array) $data->data, (array) $helper);
        $success = $this->messageIteration($data, $response);
        if ($success == BaseConsumerService::RESPONSE_SUCCESS) {
            echo self::TEXT_RESPONSE.$response->getBody(true).PHP_EOL;
        } else {
            echo self::TEXT_ERROR;
        }
    }
}
