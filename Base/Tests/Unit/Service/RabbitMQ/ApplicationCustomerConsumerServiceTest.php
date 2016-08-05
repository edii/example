<?php

namespace Araneum\Base\Traits;

use Araneum\Base\Service\ApiSenders\ApplicationApiSenderService;
use Araneum\Base\Service\RabbitMQ\ApplicationCustomerConsumerService;
use Guzzle\Service;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class ApplicationCustomerConsumerServiceTest
 *
 * @package Araneum\Base\Traits
 */
class ApplicationCustomerConsumerServiceTest extends \PHPUnit_Framework_TestCase
{
    use ConsumerTestsHelperTrait;

    const TEST_TIME_ITERATION = 20;
    const TEST_CUSTOMER_ID    = 123;
    const TEST_CREDENTIAL_URL = '/test/url';

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $applicationApiSenderService;

    /** @var ApplicationCustomerConsumerService */
    protected $apiCustomerConsumerService;

    /**
     * Set Up
     *
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->createBaseMock();
        $this->applicationApiSenderService = $this->buildMock(ApplicationApiSenderService::class);
        $this->apiCustomerConsumerService = new ApplicationCustomerConsumerService(
            $this->applicationApiSenderService,
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
        $helper = [
            'url' => $data->credential->url,
            'customerId' => $data->credential->customerId,
        ];

        $this->msgConvertHelperMock->expects($this->once())
            ->method('decodeMsg')
            ->with($this->equalTo($message->body))
            ->willReturn($data);
        $this->setMessageIterationSuccessConditions();
        $this->applicationApiSenderService->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo((array) $data->data),
                $this->equalTo((array) $helper)
            )
            ->willReturn($this->goodResponse);
        $this->expectOutputRegex('/^'.ApplicationCustomerConsumerService::TEXT_RESPONSE.'/');
        $this->apiCustomerConsumerService->execute($message);
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
                'credential' => [
                    'url' => self::TEST_CREDENTIAL_URL,
                    'customerId' => self::TEST_CUSTOMER_ID,
                ],
                'timeIteration' => self::TEST_TIME_ITERATION,
            ]
        );
    }
}
