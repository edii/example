<?php

namespace Araneum\Base\Tests\Unit\Service\RabbitMQ;

use Araneum\Base\Service\RabbitMQ\MessageConversionHelper;
use Araneum\Base\Service\RabbitMQ\ProducerService;
use Araneum\Base\Traits\MockHelperTrait;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * Class ProducerServiceTest
 *
 * @package Araneum\Base\Tests\Unit\Service\RabbitMQ
 */
class ProducerServiceTest extends \PHPUnit_Framework_TestCase
{
    use MockHelperTrait;

    const TEST_QUEUE_EXPIRATION           = '3600000000';
    const TEST_TIME_ITERATION_STRING_GOOD = '20 seconds';
    const TEST_TIME_ITERATION_STRING_BAD  = 'wrong parameter';
    const TEST_TIME_ITERATION_INT         = 20;
    const TEST_ROUTING_KEY                = 'test.route';

    /** @var array */
    private $msgBody = [];

    /** @var array */
    private $credential = [];

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $producerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $msgConvertHelperMock;

    /** @var ProducerService */
    private $producerService;

    /**
     * Set Up
     */
    protected function setUp()
    {
        $this->producerMock = $this->buildMock(Producer::class);
        $this->msgConvertHelperMock = $this->buildMock(MessageConversionHelper::class);
        $this->producerService = new ProducerService(
            $this->producerMock,
            $this->msgConvertHelperMock,
            self::TEST_QUEUE_EXPIRATION,
            self::TEST_TIME_ITERATION_STRING_GOOD,
            self::TEST_ROUTING_KEY
        );
    }

    /**
     * Test Publish method with good conditions
     */
    public function testPublishMethodNormal()
    {
        $msg = $this->getGoodMsgMock();

        $this->msgConvertHelperMock->expects($this->once())
            ->method('encodeMsg')
            ->with($this->equalTo($msg))
            ->will($this->returnValue(serialize(json_encode($msg))));

        $this->producerMock->expects($this->once())
            ->method('publish')
            ->with(
                $this->equalTo(serialize(json_encode($msg))),
                $this->equalTo(self::TEST_ROUTING_KEY),
                $this->equalTo(['expiration' => self::TEST_QUEUE_EXPIRATION])
            )
            ->will($this->returnValue(true));

        $this->assertTrue(
            $this->producerService->publish(
                $this->msgBody,
                $this->credential
            )
        );
    }

    /**
     * Test Publish method with good conditions
     *
     * @expectedException \InvalidArgumentException
     */
    public function testPublishMethodError()
    {
        $this->producerMock->expects($this->never())
            ->method('publish');

        $this->producerService->publish(
            $this->msgBody,
            $this->credential,
            self::TEST_ROUTING_KEY,
            self::TEST_TIME_ITERATION_STRING_BAD
        );
    }

    /**
     * Create massage mock
     *
     * @return \StdClass
     */
    private function getGoodMsgMock()
    {
        $msg = new \StdClass();
        $msg->data = $this->msgBody;
        $msg->credential = $this->credential;
        $msg->timeIteration = self::TEST_TIME_ITERATION_STRING_GOOD;

        return $msg;
    }
}
