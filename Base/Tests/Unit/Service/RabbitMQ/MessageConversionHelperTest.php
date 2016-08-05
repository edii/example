<?php

namespace Araneum\Base\Tests\Unit\Service\RabbitMQ;

use Araneum\Base\Service\RabbitMQ\MessageConversionHelper;

/**
 * Class MessageConversionHelperTest
 *
 * @package Araneum\Base\Tests\Unit\Service\RabbitMQ
 */
class MessageConversionHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var object */
    protected $msgBody;

    /** @var MessageConversionHelper */
    protected $msgConversionHelper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->msgBody = new \StdClass();
        $this->msgConversionHelper = new MessageConversionHelper();
    }

    /**
     * Test decodeMsg method
     */
    public function testEncodeMsg()
    {
        $this->assertEquals($this->getEncodedMsg(), $this->msgConversionHelper->encodeMsg($this->msgBody));
    }

    /**
     * Test encodeMsg method
     */
    public function testDecodeMsg()
    {
        $this->assertEquals($this->msgBody, $this->msgConversionHelper->decodeMsg($this->getEncodedMsg()));
    }

    /**
     * Get encoded message
     *
     * @return string
     */
    private function getEncodedMsg()
    {
        return serialize(json_encode($this->msgBody));
    }
}
