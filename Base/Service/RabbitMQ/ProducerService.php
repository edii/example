<?php

namespace Araneum\Base\Service\RabbitMQ;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * Class ProducerService
 *
 * @package Araneum\Base\Service\RabbitMQ
 */
class ProducerService
{
    /**
     * @var Producer
     */
    private $producer;
    /**
     * @var MessageConversionHelper
     */
    private $msgConvertHelper;
    /**
     * @var
     */
    private $queueExpiration;
    /**
     * @var
     */
    private $routingKey;
    /**
     * @var
     */
    private $timeItaration;

    /**
     * producerService constructor.
     *
     * @param Producer                $producer
     * @param MessageConversionHelper $msgConvertHelper
     * @param string                  $queueExpiration
     * @param string                  $timeItaration
     * @param string                  $routingKey
     */
    public function __construct(
        Producer $producer,
        MessageConversionHelper $msgConvertHelper,
        $queueExpiration,
        $timeItaration,
        $routingKey
    ) {
        $this->producer = $producer;
        $this->msgConvertHelper = $msgConvertHelper;
        $this->queueExpiration = $queueExpiration;
        $this->timeItaration = $timeItaration;
        $this->routingKey = $routingKey;
    }

    /**
     * Send msg to queue.
     *
     * @param mixed  $msgBody
     * @param mixed  $credential
     * @param string $routingKey
     * @param mixed  $timeIteration
     * @param array  $additionalProperties
     * @return bool|string
     */
    public function publish(
        $msgBody,
        $credential,
        $routingKey = '',
        $timeIteration = '',
        $additionalProperties = []
    ) {
        $msg = $this->getStdClass();
        $msg->data = $msgBody;
        $msg->credential = $credential;
        $msg->timeIteration = $this->manageTimeIterate($timeIteration);

        if (!empty($routingKey)) {
            $this->routingKey = $routingKey;
        }

        try {
            $this->producer->publish(
                $this->msgConvertHelper->encodeMsg($msg),
                $this->routingKey,
                array_merge($additionalProperties, ['expiration' => $this->queueExpiration])
            );

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Returns routing key of producer
     *
     * @return string
     */
    public function getRoutingKey()
    {
        return $this->routingKey;
    }

    /**
     * Making time message expiration
     *
     * @param $timeIteration
     * @return int|string
     */
    protected function manageTimeIterate($timeIteration)
    {
        if (!empty($timeIteration)) {
            if (is_string($timeIteration)) {
                $timeIteration = strtotime($timeIteration) - time();
                if (empty($timeIteration) || $timeIteration < 0) {
                    throw new InvalidParameterException(
                        'Interval daemon incorrect format (use: year, month, week, day, hours, minutes, seconds).'
                    );
                }
            }

            $this->timeItaration = $timeIteration;
        }

        return $this->timeItaration;
    }

    /**
     * Get \stdClass object
     *
     * @return \stdClass
     */
    private function getStdClass()
    {
        return new \stdClass();
    }
}
