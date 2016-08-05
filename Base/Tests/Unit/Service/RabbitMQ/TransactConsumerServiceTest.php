<?php

namespace  Araneum\Base\Traits;

use Araneum\Base\Service\ApiSenders\TransactApiSenderService;
use Araneum\Base\Service\RabbitMQ\TransactConsumerService;
use Guzzle\Service;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class TransactConsumerServiceTest
 *
 * @package Araneum\Base\Traits
 */
class TransactConsumerServiceTest extends \PHPUnit_Framework_TestCase
{
    use ConsumerTestsHelperTrait;

    const TEST_TIME_ITERATION = 20;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $transactApiSenderServiceMock;

    /** @var TransactConsumerService */
    protected $transactConsumerService;

    /**
     * Set Up
     *
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->createBaseMock();
        $this->transactApiSenderServiceMock = $this->buildMock(TransactApiSenderService::class);
        $this->transactConsumerService = new TransactConsumerService(
            $this->transactApiSenderServiceMock,
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
        $this->transactApiSenderServiceMock->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo((array) $data->data),
                $this->equalTo((array) $data->credential)
            )
            ->willReturn($this->goodResponse);

        $this->expectOutputRegex('/^'.TransactConsumerService::TEXT_RESPONSE.'/');
        $this->transactConsumerService->execute($message);
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
