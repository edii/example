<?php
namespace Araneum\Base\Traits;

use Araneum\Base\Service\RabbitMQ\MessageConversionHelper;
use Guzzle\Http\Message\Response;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Araneum\Base\Service\RabbitMQ\ProducerService;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class ConsumerTestsHelperTrait
 *
 * @package Araneum\Base\Traits
 */
trait ConsumerTestsHelperTrait
{
    use MockHelperTrait;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $producerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $deadMessagesProducerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $msgConvertHelperMock;

    /** @var Response */
    protected $goodResponse;

    /** @var Response */
    protected $badResponse;

    /** @var \Exception */
    protected $exceptionResponse;

    /** @var Response */
    protected $serverErrorResponse;

    /**
     * Init Response templates
     */
    protected function initResponses()
    {
        $this->goodResponse = (new Response(200))->setBody('good response');
        $this->badResponse = (new Response(404))->setBody('bad response');
        $this->serverErrorResponse = (new Response(500))->setBody('server error response');
        $this->exceptionResponse = new \Exception('exception message');
    }

    /**
     * Create base Mock for all Consumers
     */
    protected function createBaseMock()
    {
        $this->initResponses();
        $this->producerMock = $this->buildMock(ProducerService::class);
        $this->deadMessagesProducerMock = $this->buildMock(Producer::class);
        $this->msgConvertHelperMock = $this->buildMock(MessageConversionHelper::class);
    }

    /**
     * Define conditions for messageIteration method with Success result
     */
    protected function setMessageIterationSuccessConditions()
    {
        $this->producerMock->expects($this->never())
            ->method('publish');
        $this->deadMessagesProducerMock->expects($this->never())
            ->method('publish');
    }

    /**
     * Define conditions for messageIteration method with dead message result
     */
    protected function setMessageIterationDeadMessageConditions()
    {
        $this->producerMock->expects($this->never())
            ->method('publish');
        $this->deadMessagesProducerMock->expects($this->any())
            ->method('publish')
            ->will($this->returnValue(null));
    }

    /**
     * Define conditions for messageIteration method with message republished result
     */
    protected function setMessageIterationRepublishConditions()
    {
        $this->deadMessagesProducerMock->expects($this->never())
            ->method('publish');
        $this->producerMock->expects($this->once())
            ->method('publish')
            ->will($this->returnValue(null));
    }

    /**
     * Create RabbitMQ massage with Data given in messageBody
     *
     * @param array $messageBody
     * @return $this
     */
    protected function getRabbitMQMessageWithData(array $messageBody = [])
    {
        return (new AMQPMessage())
            ->setBody(
                serialize(
                    json_encode($messageBody)
                )
            );
    }
}
