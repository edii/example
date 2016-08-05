<?php

namespace Araneum\Base\Service\Factories;

use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Araneum\Base\Service\RabbitMQ\MessageConversionHelper;
use Araneum\Base\Service\RabbitMQ\ProducerService;

/**
 * Abstract class AbstractApiSender
 *
 * @package Araneum\Base\Service
 */
class ProducerFactory
{
    /**
     * Creates an producer by input params
     * @param Producer $producer
     * @param string   $queueExpiration
     * @param string   $messageTimeIteration
     * @param string   $routingKey
     * @return mixed
     *
     */
    public function createService(Producer $producer, $queueExpiration, $messageTimeIteration, $routingKey)
    {
        return new ProducerService($producer, new MessageConversionHelper(), $queueExpiration, $messageTimeIteration, $routingKey);
    }
}
