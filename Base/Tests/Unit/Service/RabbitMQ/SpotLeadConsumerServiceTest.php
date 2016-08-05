<?php

namespace Araneum\Base\Tests\Unit\Service\RabbitMQ;

use Araneum\Base\Service\ApiSenders\SpotApiSenderService;
use Araneum\Base\Service\RabbitMQ\SpotConsumerService;
use Araneum\Base\Service\RabbitMQ\SpotLeadConsumerService;
use Araneum\Base\Traits\ConsumerTestsHelperTrait;
use Araneum\Bundle\AgentBundle\Entity\Lead;
use Araneum\Bundle\AgentBundle\Repository\LeadRepository;
use Doctrine\ORM\EntityManager;
use Guzzle\Http\Message\Response;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class DeadMessagesConsumerServiceTest
 *
 * @package Araneum\Base\Tests\Unit\Service\RabbitMQ
 */
class SpotLeadConsumerServiceTest extends \PHPUnit_Framework_TestCase
{
    use ConsumerTestsHelperTrait;

    const TEST_TIME_ITERATION = 20;

    const LEAD_ID = 1023123;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $spotApiSenderServiceMock;

    /** @var SpotConsumerService */
    protected $spotLeadConsumerService;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $eventDispatcherMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $entityManagerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $leadRepositoryMock;

    /**
     * @var Lead
     */
    protected $lead;

    /**
     * Set Up
     *
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->lead = new Lead();
        $this->createBaseMock();
        $this->createDoctrineMock();
        $this->createDispatcherMock();
        $this->spotApiSenderServiceMock = $this->buildMock(SpotApiSenderService::class);
        $this->spotLeadConsumerService = new SpotLeadConsumerService(
            $this->spotApiSenderServiceMock,
            $this->producerMock,
            $this->msgConvertHelperMock,
            $this->entityManagerMock,
            $this->eventDispatcherMock,
            $this->deadMessagesProducerMock,
            false
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
                $this->equalTo((array) json_decode(unserialize($data))->credential)
            )
            ->willReturn($this->goodResponse);
        $this->setMessageIterationSuccessConditions();
        $this->spotLeadConsumerService->execute($message);
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
                    'log' => [
                        'leadId' => self::LEAD_ID,
                    ],
                ],
                'timeIteration' => self::TEST_TIME_ITERATION,
            ]
        );
    }

    /**
     * Create Doctrine classes mock
     */
    private function createDoctrineMock()
    {
        $this->entityManagerMock = $this->buildMock(EntityManager::class);

        $this->leadRepositoryMock = $this->buildMock(
            LeadRepository::class,
            true,
            ['findOneById']
        );
        $this->entityManagerMock->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('AraneumAgentBundle:Lead'))
            ->willReturn($this->leadRepositoryMock);
        $this->leadRepositoryMock->expects($this->any())
            ->method('findOneById')
            ->with($this->equalTo(self::LEAD_ID))
            ->willReturn($this->lead);

        $this->entityManagerMock->expects($this->any())->method('persist');
        $this->entityManagerMock->expects($this->any())->method('flush');
    }

    /**
     * Create Doctrine classes mock
     */
    private function createDispatcherMock()
    {
        $this->eventDispatcherMock = $this->buildMock(EventDispatcher::class);
        $this->eventDispatcherMock->expects($this->any())->method('dispatch');
    }
}
