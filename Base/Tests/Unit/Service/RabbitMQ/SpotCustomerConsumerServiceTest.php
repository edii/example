<?php

namespace Araneum\Base\Tests\Unit\Service\RabbitMQ;

use Araneum\Base\Service\ApiSenders\SpotApiSenderService;
use Araneum\Base\Service\RabbitMQ\BaseConsumerService;
use Araneum\Base\Service\RabbitMQ\SpotCustomerConsumerService;
use Araneum\Base\Traits\ConsumerTestsHelperTrait;
use Araneum\Bundle\AgentBundle\AgentEvents;
use Araneum\Bundle\AgentBundle\Entity\Customer;
use Araneum\Bundle\AgentBundle\Entity\CustomerLog;
use Araneum\Bundle\AgentBundle\Event\CustomerEvent;
use Araneum\Bundle\AgentBundle\Repository\CustomerRepository;
use Araneum\Bundle\MainBundle\Entity\Application;
use Doctrine\ORM\EntityManager;
use Gedmo\Exception\RuntimeException;
use Guzzle\Http\Message\Response;
use PhpAmqpLib\Message\AMQPMessage;
use Araneum\Bundle\AgentBundle\Entity\Agent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class SpotCustomerConsumerServiceTest
 *
 * @package Araneum\Base\Tests\Unit\Service\RabbitMQ
 */
class SpotCustomerConsumerServiceTest extends \PHPUnit_Framework_TestCase
{
    use ConsumerTestsHelperTrait;

    const TEST_CUSTOMER_ID = 1;
    const TEST_APPLICATION_ID = 11;
    const TEST_SPOT_CUSTOMER_ID = 13;
    const TEST_CUSTOMER_PASSWORD = 'tesPass123';
    const TEST_TIME_ITERATION = 20;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $spotApiSenderServiceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $entityManagerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $customerRepositoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $eventDispatcherMock;

    /** @var Customer */
    protected $customer;

    /** @var Application */
    protected $application;

    /** @var Agent */
    protected $agent;

    /** @var AMQPMessage */
    protected $message;

    /** @var object */
    protected $data;

    /** @var SpotCustomerConsumerService */
    protected $spotCustomerConsumerService;

    /**
     * Set Up
     *
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->customer = new Customer();
        $this->application = new Application();
        $this->agent = (new Agent())->setType(Agent::SPOTOPTION);
        $this->application->addAgent($this->agent);
        $this->createBaseMock();
        $this->createDoctrineMock();
        $this->spotApiSenderServiceMock = $this->buildMock(SpotApiSenderService::class);
        $this->eventDispatcherMock = $this->buildMock(EventDispatcherInterface::class);
        $this->spotCustomerConsumerService = new SpotCustomerConsumerService(
            $this->spotApiSenderServiceMock,
            $this->producerMock,
            $this->msgConvertHelperMock,
            $this->entityManagerMock,
            $this->eventDispatcherMock,
            $this->deadMessagesProducerMock
        );
    }

    /**
     * Test Execute method with success Create Customer params
     */
    public function testCreateCustomerSuccess()
    {
        $this->message = $this->createRabbitMQMessage(CustomerLog::ACTION_CREATE);
        $this->setMessageIterationSuccessConditions();
        $this->setSameConditions($this->goodResponse);
        $this->spotApiSenderServiceMock->expects($this->once())
            ->method('getErrors')
            ->with($this->equalTo($this->goodResponse))
            ->willReturn(null);
        $this->eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->equalTo(AgentEvents::CUSTOMER_LOGIN),
                $this->equalTo(
                    (new CustomerEvent())
                        ->setCustomer($this->customer)
                        ->setHeaders([
                            'referer' => 'referer',
                        ])
                )
            );
        $this->spotCustomerConsumerService->execute($this->message);
    }

    /**
     * Test Execute method with success Update Customer params
     */
    public function testUpdateCustomerSuccess()
    {
        $this->message = $this->createRabbitMQMessage(CustomerLog::ACTION_UPDATE);
        $this->setSameConditions($this->goodResponse);
        $this->setMessageIterationSuccessConditions();
        $this->spotApiSenderServiceMock->expects($this->once())
            ->method('getErrors')
            ->with($this->equalTo($this->goodResponse))
            ->willReturn(null);
        $this->spotCustomerConsumerService->execute($this->message);
    }

    /**
     * Test Execute method with spotResponse errors
     */
    public function testExecuteSendToSpotError()
    {
        $this->message = $this->createRabbitMQMessage();
        $this->setSameConditions($this->goodResponse);
        $this->spotApiSenderServiceMock->expects($this->exactly(1))
            ->method('getErrors')
            ->with($this->equalTo($this->goodResponse))
            ->willReturn('not null value - error');
        $this->deadMessagesProducerMock->expects($this->exactly(1))
            ->method('publish');

        $this->assertContains(
            BaseConsumerService::DEAD_MSG_ROUTING_KEY,
            $this->spotCustomerConsumerService->execute($this->message)
        );
    }

    /**
     * Set success conditions that repeats in create and update tests
     *
     * @param Response|\Exception $response
     */
    private function setSameConditions($response)
    {
        $data = json_decode(unserialize($this->message->body));
        $this->msgConvertHelperMock->expects($this->once())
            ->method('decodeMsg')
            ->with($this->equalTo($this->message->body))
            ->willReturn($data);

        if ($response instanceof Response) {
            $response->setBody(json_encode($this->createSpotResponseMessage()));
        }
        $this->spotApiSenderServiceMock->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo((array) $data->data),
                $this->equalTo((array) $data->credential)
            )
            ->willReturn($response);
    }

    /**
     * Create RabbitMQ massage
     *
     * @param string $action
     * @return AMQPMessage
     */
    private function createRabbitMQMessage($action = '')
    {
        return $this->getRabbitMQMessageWithData(
            [
                'data' => [
                    'password' => self::TEST_CUSTOMER_PASSWORD,
                ],
                'credential' => [
                    'credentials' => $this->application->getAgentByType(Agent::SPOTOPTION)->getCredentials(),
                    'log' => [
                        'action' => $action,
                        'customerId' => self::TEST_CUSTOMER_ID,
                        'applicationId' => self::TEST_APPLICATION_ID,
                        'headers' => [
                            'referer' => 'referer',
                        ],
                    ],
                ],
                'timeIteration' => self::TEST_TIME_ITERATION,
            ]
        );
    }

    /**
     * Create Spot Response message
     *
     * @return array
     */
    private function createSpotResponseMessage()
    {
        return [
            'status' => [
                'Customer' => [
                    'data_id' => self::TEST_SPOT_CUSTOMER_ID,
                ],
            ],
        ];
    }

    /**
     * Create Doctrine classes mock
     */
    private function createDoctrineMock()
    {
        $this->customerRepositoryMock = $this->buildMock(
            CustomerRepository::class,
            true,
            ['findOneById']
        );
        $this->entityManagerMock = $this->buildMock(EntityManager::class);
        $this->entityManagerMock->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('AraneumAgentBundle:Customer'))
            ->willReturn($this->customerRepositoryMock);
        $this->customerRepositoryMock->expects($this->any())
            ->method('findOneById')
            ->with($this->equalTo(self::TEST_CUSTOMER_ID))
            ->willReturn($this->customer);
        $this->entityManagerMock->expects($this->any())
            ->method('getReference')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue($this->application),
                    $this->returnValue($this->customer)
                )
            );
        $this->entityManagerMock->expects($this->any())->method('persist');
        $this->entityManagerMock->expects($this->any())->method('flush');
    }
}
