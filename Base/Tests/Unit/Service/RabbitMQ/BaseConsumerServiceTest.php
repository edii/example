<?php

namespace Araneum\Base\Tests\Unit\Service\RabbitMQ;

use Araneum\Base\Service\RabbitMQ\BaseConsumerService;
use Araneum\Base\Traits\ConsumerTestsHelperTrait;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class BaseConsumerServiceTest
 *
 * @package Araneum\Base\Tests\Unit\Service\RabbitMQ
 */
class BaseConsumerServiceTest extends \PHPUnit_Framework_TestCase
{
    use ConsumerTestsHelperTrait;

    const RESULT_DEAD_MSG    = 'Message was published to dead messages queue';
    const RESULT_REPUBLISHED = 'Message was republished';
    const RESULT_SUCCCESS    = 'Success';

    /** @var BaseConsumerService */
    protected $baseConsumerService;

    /** @var object */
    protected $dataObject;

    /**
     * Set Up
     *
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->generateDataObjectMock();
        $this->createBaseMock();
        $this->deadMessagesProducerMock->expects($this->any())
            ->method('publish')
            ->with(
                $this->anything(),
                $this->equalTo(BaseConsumerService::DEAD_MSG_ROUTING_KEY)
            )
            ->will($this->returnValue(self::RESULT_DEAD_MSG));

        $this->producerMock->expects($this->any())
            ->method('publish')
            ->with(
                $this->equalTo($this->dataObject->data),
                $this->equalTo($this->dataObject->credential),
                $this->equalTo(null),
                $this->anything()
            )
            ->will($this->returnValue(self::RESULT_REPUBLISHED));

        $this->baseConsumerService = new BaseConsumerService(
            $this->producerMock,
            $this->msgConvertHelperMock,
            $this->deadMessagesProducerMock
        );
    }

    /**
     * Test all possible results of method messageIteration
     *
     * @param int                 $timeIteration
     * @param Response|\Exception $response
     * @param string              $expected
     * @dataProvider dataForMessageIterationMethod
     */
    public function testMessageIterationMethod($timeIteration, $response, $expected)
    {
        $this->dataObject->timeIteration = $timeIteration;
        $this->assertEquals(
            $expected,
            $this->baseConsumerService->messageIteration($this->dataObject, $response)
        );
    }

    /**
     * Get data for testing messageIteration method
     *
     * @return array
     */
    public function dataForMessageIterationMethod()
    {
        $this->initResponses();
        $expired = time() - 1;
        $notExpired = strtotime('20 second');

        return [
            'Test \Exception Response' => [
                $notExpired,
                $this->exceptionResponse,
                self::RESULT_DEAD_MSG,
            ],
            'Test server error Response with expired message' => [
                $expired,
                $this->serverErrorResponse,
                self::RESULT_DEAD_MSG,
            ],
            'Test server error Response with not expired message (republished)' => [
                $notExpired,
                $this->serverErrorResponse,
                self::RESULT_REPUBLISHED,
            ],
            'Test bad Response with message to republish' => [
                $notExpired,
                $this->badResponse,
                self::RESULT_DEAD_MSG,
            ],
            'Test Success Response' => [
                $notExpired,
                $this->goodResponse,
                self::RESULT_SUCCCESS,
            ],
        ];
    }

    /**
     * Create data argument for messageIteration method
     */
    private function generateDataObjectMock()
    {
        $this->dataObject = new \StdClass();
        $this->dataObject->data = [];
        $this->dataObject->credential = [];
        $this->dataObject->timeIteration = time();
    }
}
