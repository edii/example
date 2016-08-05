<?php

namespace Araneum\Base\Tests\Unit\Service\RabbitMQ;

use Araneum\Base\Service\ApiSenders\ApplicationApiSenderService;
use Araneum\Base\Service\ApiSenders\SpotApiSenderService;
use Araneum\Base\Service\RabbitMQ\SpotCustomerConsumerService;
use Araneum\Base\Service\RabbitMQ\SpotCustomerLoginConsumerService;
use Araneum\Base\Traits\ConsumerTestsHelperTrait;
use Araneum\Bundle\AgentBundle\Entity\Agent;
use Araneum\Bundle\AgentBundle\Entity\Customer;
use Araneum\Bundle\AgentBundle\Repository\CustomerRepository;
use Araneum\Bundle\MainBundle\Entity\Application;
use Araneum\Bundle\MainBundle\Service\RemoteApplicationManagerService;
use Doctrine\ORM\EntityManager;
use Guzzle\Http\Message\Response;
use JMS\Serializer\SerializerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SpotCustomerLoginConsumerServiceTest
 *
 * @package Araneum\Base\Tests\Unit\Service\RabbitMQ
 */
class SpotCustomerLoginConsumerServiceTest extends \PHPUnit_Framework_TestCase
{
    use ConsumerTestsHelperTrait;

    const TEST_SPOT_API_PUBLIC_URL       = 'public/url';
    const TEST_TIME_ITERATION            = 20;
    const TEST_CUSTOMER_ID               = 123;
    const TEST_CUSTOMER_USER_AGENT       = 'test User-Agent header';
    const TEST_CUSTOMER_IP               = '177.168.219.99';
    const TEST_CUSTOMER_EMAIL            = 'test@test.com';
    const TEST_CUSTOMER_PASSWORD         = 'testPassword123';
    const TEST_SPOT_SESSION              = 'fa7464ce80fd146790f71bcacf908c4ace';

    protected $headers = [
        'User-Agent' => self::TEST_CUSTOMER_USER_AGENT,
        'X-Forwarded-For' => self::TEST_CUSTOMER_IP,
    ];

    protected $requestData = [
        'email' => self::TEST_CUSTOMER_EMAIL,
        'password' => self::TEST_CUSTOMER_PASSWORD,
        'spotsession' => self::TEST_SPOT_SESSION,
    ];

    protected $spotCustomerData = [
        'customerId' => self::TEST_CUSTOMER_ID,
        'spotsession' => self::TEST_SPOT_SESSION,
    ];

    protected $log;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $spotApiSenderServiceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $entityManagerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $customerRepositoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $serializerInterfaceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $applicationApiSenderService;

    /** @var Customer */
    protected $customer;

    /** @var SpotCustomerConsumerService */
    protected $spotCustomerLoginConsumerService;

    /**
     * Set Up
     *
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->createTestCustomer();
        $this->createBaseMock();
        $this->createDoctrineMock();
        $this->spotApiSenderServiceMock = $this->buildMock(SpotApiSenderService::class);
        $this->serializerInterfaceMock = $this->buildMock(SerializerInterface::class);
        $this->applicationApiSenderService = $this->buildMock(ApplicationApiSenderService::class);
        $this->spotCustomerLoginConsumerService = new SpotCustomerLoginConsumerService(
            $this->spotApiSenderServiceMock,
            $this->producerMock,
            $this->msgConvertHelperMock,
            $this->entityManagerMock,
            $this->serializerInterfaceMock,
            $this->applicationApiSenderService,
            $this->deadMessagesProducerMock
        );
    }

    /**
     * Test Execute method with success Create Customer params
     */
    public function testCustomerLoginSuccess()
    {
        $message = $this->createRabbitMQMessage();
        $data = json_decode(unserialize($message->body));

        $this->msgConvertHelperMock->expects($this->once())
            ->method('decodeMsg')
            ->with($this->equalTo($message->body))
            ->willReturn($data)
        ;

        $this->serializerInterfaceMock->expects($this->once())
            ->method('deserialize')
            ->with(
                $this->equalTo($data->data),
                $this->equalTo('Araneum\Bundle\AgentBundle\Entity\Customer'),
                $this->equalTo('json')
            )
            ->willReturn($this->customer);

        $this->spotApiSenderServiceMock->expects($this->once())
            ->method('generateSpotSession')
            ->willReturn(self::TEST_SPOT_SESSION);

        $this->log = [
            'applicationId' =>  $this->customer->getApplication()->getId(),
            'agentId' => $this->customer->getApplication()->getAgentByType(Agent::SPOTOPTION)->getId(),
        ];

        $this->goodResponse->setBody($this->createSpotResponseMessage());

        $this->spotApiSenderServiceMock->expects($this->once())
            ->method('sendToPublicUrl')
            ->with(
                $this->equalTo(Request::METHOD_POST),
                $this->equalTo(self::TEST_SPOT_API_PUBLIC_URL),
                $this->logicalOr(
                    $this->equalTo($this->headers),
                    []
                ),
                $this->equalTo($this->requestData),
                $this->equalTo($this->log)
            )
            ->willReturn($this->goodResponse);
        $this->setMessageIterationSuccessConditions();

        $this->spotApiSenderServiceMock->expects($this->once())
            ->method('getErrorsFromPublic')
            ->with($this->equalTo($this->goodResponse))
            ->willReturn(null);

        $this->applicationApiSenderService->expects($this->once())
            ->method('send');

        $this->entityManagerMock->expects($this->any())
            ->method('getReference')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue($this->customer->getApplication()),
                    $this->returnValue($this->customer->getApplication()->getAgentByType(Agent::SPOTOPTION)),
                    $this->returnValue($this->customer->getApplication()),
                    $this->returnValue($this->customer)
                )
            );

        $this->spotCustomerLoginConsumerService->execute($message);
    }

    /**
     * Create Customer with test data
     */
    private function createTestCustomer()
    {
        $agent = (new Agent())
            ->setPublicUrl(self::TEST_SPOT_API_PUBLIC_URL)
            ->setType(Agent::SPOTOPTION);
        $application = (new Application())
            ->addAgent($agent);
        $this->customer = (new Customer())
            ->setApplication($application)
            ->setUserAgent(self::TEST_CUSTOMER_USER_AGENT)
            ->setIp(self::TEST_CUSTOMER_IP)
            ->setEmail(self::TEST_CUSTOMER_EMAIL)
            ->setPassword(self::TEST_CUSTOMER_PASSWORD);
    }

    /**
     * Create Spot Response message
     *
     * @return array
     */
    private function createSpotResponseMessage()
    {
        return json_encode(
            [
                'customerId' => self::TEST_CUSTOMER_ID,
            ]
        );
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
                    'credential' => [],
                    'log' => [
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
     * Create Doctrine classes mock
     */
    private function createDoctrineMock()
    {
        $this->customerRepositoryMock = $this->buildMock(
            CustomerRepository::class,
            true,
            ['find']
        );
        $this->entityManagerMock = $this->buildMock(EntityManager::class);
        $this->entityManagerMock->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('AraneumAgentBundle:Customer'))
            ->willReturn($this->customerRepositoryMock);
        $this->customerRepositoryMock->expects($this->any())
            ->method('find')
            ->willReturn($this->customer);
        $this->entityManagerMock->expects($this->any())->method('persist');
        $this->entityManagerMock->expects($this->any())->method('flush');
    }
}
