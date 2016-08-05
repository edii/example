<?php

namespace Araneum\Base\Service\RabbitMQ;

/**
 * Class DataConvertHelper
 *
 * @package Araneum\Base\Service\RabbitMQ
 */
class MessageConversionHelper
{
    /**
     * Convert message for send
     *
     * @param  object $msg
     * @return string
     */
    public function encodeMsg($msg)
    {
        return serialize(json_encode($msg));
    }

    /**
     * Convert message for send
     *
     * @param  string $msg
     * @return object
     */
    public function decodeMsg($msg)
    {
        return json_decode(unserialize($msg));
    }
}
