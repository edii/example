<?php

namespace Araneum\Base\Tests\Unit\Service\RabbitMQ;

use Araneum\Base\Service\ApiSenders\SpotApiSenderService;
use Araneum\Base\Service\RabbitMQ\SpotConsumerService;
use Araneum\Base\Traits\ConsumerTestsHelperTrait;
use Guzzle\Http\Message\Response;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class DeadMessagesConsumerServiceTest
 *
 * @package Araneum\Base\Tests\Unit\Service\RabbitMQ
 */
class SpotConsumerServiceTest extends \PHPUnit_Framework_TestCase
{
    use ConsumerTestsHelperTrait;

    const TEST_TIME_ITERATION = 20;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $spotApiSenderServiceMock;

    /** @var SpotConsumerService */
    protected $spotConsumerService;

    /**
     * Set Up
     *
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->createBaseMock();
        $this->spotApiSenderServiceMock = $this->buildMock(SpotApiSenderService::class);
        $this->spotConsumerService = new SpotConsumerService(
            $this->spotApiSenderServiceMock,
            $this->producerMock,
            $this->msgConvertHelperMock,
            $this->deadMessagesProducerMock
        );
    }

    /**
     * Test execute method with success result
     */
    public function testExecuteMethodSuccess()
    {
        $message = $this->createRabbitMQMessage();
        $data = $message->body;
        $this->msgConvertHelperMock->expects($this->once())
            ->method('decodeMsg')
            ->with($this->equalTo($data))
            ->willReturn(json_decode(unserialize($data)));
        $this->spotApiSenderServiceMock->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo([]),
                $this->equalTo([])
            )
            ->willReturn($this->goodResponse);
        $this->setMessageIterationSuccessConditions();
        $this->expectOutputRegex('/^'.SpotConsumerService::TEXT_RESPONSE.'/');
        $this->spotConsumerService->execute($message);
    }

    /**
     * Create RabbitMQ massage
     *
     * @return AMQPMessage
     */
    private function createRabbitMQMessage()
    {
        return $this->getRabbitMQMessageWithData(
            [
                'data' => [],
                'credential' => [],
                'timeIteration' => self::TEST_TIME_ITERATION,
            ]
        );
    }
}
