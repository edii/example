<?php

namespace Araneum\Base\Tests\Unit\Service\RabbitMQ;

use Araneum\Base\Service\RabbitMQ\DeadMessagesConsumerService;
use Araneum\Base\Service\RabbitMQ\MessageConversionHelper;
use Araneum\Base\Traits\MockHelperTrait;
use Araneum\Bundle\UserBundle\Entity\User;
use Araneum\Bundle\UserBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DeadMessagesConsumerServiceTest
 *
 * @package Araneum\Base\Tests\Unit\Service\RabbitMQ
 */
class DeadMessagesConsumerServiceTest extends \PHPUnit_Framework_TestCase
{
    use MockHelperTrait;
    const TEST_BODY = [
        'property_1' => 'test string body 1',
        'property_2' => 'test string body 2',
    ];
    const TEST_ROUTING_KEY = 'test.routing.key';
    const SEND_SUCCESSFUL = 0;

    /**
     * Set content type
     *
     * @var array
     */
    protected static $contentTypes = [
        'html' => 'text/html',
        'plain' => 'text/plain',
    ];

    /** @var DeadMessagesConsumerService */
    protected $deadMessagesConsumer;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $msgConvHelperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $twigServiceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $containerInterfaceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $entityManagerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $userRepositoryMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mailerMock;

    /** @var User */
    protected $user;

    /**
     * Set Up
     *
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->user = (new User())
            ->setRole(User::ROLE_ADMIN)
            ->setEmail('test@test.com');

        $this->msgConvHelperMock = $this->buildMock(MessageConversionHelper::class);
        $this->userRepositoryMock = $this->buildMock(UserRepository::class);

        $this->userRepositoryMock->expects($this->once())
            ->method('getAllowedToReceiveSystemEmails')
            ->will($this->returnValue([$this->user]));

        $this->mailerMock = $this->getMockBuilder(\Swift_Mailer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->createTwigServiceMock();
        $this->createEntityManagerMock();
        $this->createContainerInterfaceMock();

        $this->deadMessagesConsumer = new DeadMessagesConsumerService(
            $this->msgConvHelperMock,
            $this->containerInterfaceMock
        );
    }

    /**
     * Test execute method
     */
    public function testExecuteMethod()
    {
        $message = $this->createMsg();

        $this->mailerMock->expects($this->any())
            ->method('send')
            ->will($this->returnValue($this->equalTo(self::SEND_SUCCESSFUL)));

        $this->deadMessagesConsumer->execute($message);
    }

    /**
     * Create Twig Service Mock
     */
    private function createTwigServiceMock()
    {
        $this->twigServiceMock = $this->getMockBuilder(TwigEngine::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->twigServiceMock->expects($this->once())
            ->method('render')
            ->will($this->returnValue('Rendering template'));
    }

    /**
     * Get container interface Mock
     */
    private function createContainerInterfaceMock()
    {
        $this->containerInterfaceMock = $this->getMockBuilder(ContainerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->containerInterfaceMock->expects($this->exactly(3))
            ->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue($this->entityManagerMock),
                    $this->returnValue($this->twigServiceMock),
                    $this->returnValue($this->mailerMock)
                )
            );
    }

    /**
     * Get Entity Manager Mock
     */
    private function createEntityManagerMock()
    {
        $this->entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManagerMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('AraneumUserBundle:User'))
            ->will($this->returnValue($this->userRepositoryMock));

        $this->entityManagerMock->expects($this->any())
            ->method('persist');

        $this->entityManagerMock->expects($this->any())
            ->method('flush');
    }

    /**
     * Get Entity Manager Mock
     *
     * @return AMQPMessage;
     */
    private function createMsg()
    {
        $message = new \StdClass();

        $message->data = json_encode(self::TEST_BODY);
        $message->routingKey = self::TEST_ROUTING_KEY;

        return new AMQPMessage(json_encode($message));
    }
}
