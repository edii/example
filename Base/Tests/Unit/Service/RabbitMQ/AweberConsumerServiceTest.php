<?php

namespace  Araneum\Base\Traits;

use Araneum\Base\Service\ApiSenders\AweberApiSenderService;
use Araneum\Base\Service\RabbitMQ\AweberConsumerService;
use Guzzle\Service;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AweberConsumerServiceTest
 *
 * @package Araneum\Base\Traits
 */
class AweberConsumerServiceTest extends \PHPUnit_Framework_TestCase
{
    use ConsumerTestsHelperTrait;

    const TEST_TIME_ITERATION = 20;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $aweberApiSenderServiceMock;

    /** @var AweberConsumerService */
    protected $aweberConsumerService;

    /**
     * Set Up
     *
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->createBaseMock();
        $this->aweberApiSenderServiceMock = $this->buildMock(AweberApiSenderService::class);
        $this->aweberConsumerService = new AweberConsumerService(
            $this->aweberApiSenderServiceMock,
            $this->producerMock,
            $this->msgConvertHelperMock,
            $this->deadMessagesProducerMock
        );
    }

    /**
     * Test Execute method with success
     */
    public function testExecuteSuccess()
    {
        $message = $this->createRabbitMQMessage();
        $data = json_decode(unserialize($message->body));
        $this->msgConvertHelperMock->expects($this->once())
            ->method('decodeMsg')
            ->with($this->equalTo($message->body))
            ->willReturn($data);
        $this->setMessageIterationSuccessConditions();
        $this->aweberApiSenderServiceMock->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo((array) $data->data),
                $this->equalTo((array) $data->credential)
            )
            ->willReturn($this->goodResponse);
        $this->expectOutputRegex('/^'.AweberConsumerService::TEXT_RESPONSE.'/');
        $this->aweberConsumerService->execute($message);
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
