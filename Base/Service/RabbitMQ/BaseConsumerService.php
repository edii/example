<?php

namespace Araneum\Base\Service\RabbitMQ;

use Guzzle\Http\Message\Response;
use Guzzle\Service;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

/**
 * Class SpotConsumerService
 *
 * @package Araneum\Base\Service\RabbitMQ
 */
class BaseConsumerService
{
    const RESPONSE_SUCCESS     = 'Success';
    const DEAD_MSG_ROUTING_KEY = 'dead.message';

    /**
     * @var MessageConversionHelper
     */
    protected $msgConvertHelper;

    /**
     * @var Producer
     */
    protected $deadMessagesProducer;

    /**
     * @var ProducerService
     */
    protected $producer;

    /**
     * Consumer constructor.
     *
     * @param ProducerService         $producer
     * @param MessageConversionHelper $msgConvertHelper
     * @param Producer                $deadMessagesProducer
     */
    public function __construct(
        ProducerService $producer,
        MessageConversionHelper $msgConvertHelper,
        Producer $deadMessagesProducer
    ) {
        $this->producer = $producer;
        $this->msgConvertHelper = $msgConvertHelper;
        $this->deadMessagesProducer = $deadMessagesProducer;
    }

    /**
     * Message Iteration
     *
     * @param mixed $data
     * @param mixed $answer
     *
     * @return mixed
     */
    public function messageIteration($data, $answer)
    {
        if ($answer instanceof \Exception) {
            $data->logs = [
                'exceptionCode' => $answer->getCode(),
                'message' => $answer->getMessage(),
            ];

            return $this->deadMessagesProducer->publish(
                $this->createDataForDeadMessagesQueue($data),
                self::DEAD_MSG_ROUTING_KEY
            );
        } elseif ($answer instanceof Response && $answer->getStatusCode() >= 400 && $answer->getStatusCode() < 500) {
            $data->logs = [
                'statusCode' => $answer->getStatusCode(),
                'message' => $answer->getBody(),
            ];

            return $this->deadMessagesProducer->publish(
                $this->createDataForDeadMessagesQueue($data),
                self::DEAD_MSG_ROUTING_KEY
            );
        } elseif ($answer instanceof Response && ($answer->getStatusCode() < 200 || $answer->getStatusCode() >= 300)) {
            if ($data->timeIteration > time()) {
                return $this->producer->publish($data->data, $data->credential, null, $data->timeIteration);
            } else {
                $data->logs = [
                    'statusCode' => $answer->getStatusCode(),
                    'message' => $answer->getBody(),
                ];

                return $this->deadMessagesProducer->publish(
                    $this->createDataForDeadMessagesQueue($data),
                    self::DEAD_MSG_ROUTING_KEY
                );
            }
        } else {
            return self::RESPONSE_SUCCESS;
        }
    }

    /**
     * Get message for dead messages queue
     *
     * @param mixed $data
     * @return \stdClass
     */
    protected function createDataForDeadMessagesQueue($data)
    {
        $stdClass = $this->getStdClass();
        $stdClass->routingKey = $this->producer->getRoutingKey();
        $stdClass->data = json_encode($data);

        return json_encode($stdClass);
    }

    /**
     * Get \stdClass object
     *
     * @return \stdClass
     */
    protected function getStdClass()
    {
        return new \stdClass();
    }
}
