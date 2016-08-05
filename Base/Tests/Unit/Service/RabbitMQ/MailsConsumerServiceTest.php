<?php

namespace Araneum\Base\Tests\Unit\Service\RabbitMQ;

use Araneum\Base\Service\RabbitMQ\MailsConsumerService;
use Araneum\Base\Service\RabbitMQ\MessageConversionHelper;
use Araneum\Base\Traits\MockHelperTrait;
use Araneum\Bundle\MailBundle\Entity\Mail;
use Araneum\Bundle\MailBundle\Entity\MailLog;
use Araneum\Bundle\MailBundle\Repository\MailLogRepository;
use Araneum\Bundle\MailBundle\Repository\MailRepository;
use Araneum\Bundle\MailBundle\Service\MailsSenderService;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class MailsConsumerServiceTest
 *
 * @package Araneum\Base\Tests\Unit\Service\RabbitMQ
 */
class MailsConsumerServiceTest extends \PHPUnit_Framework_TestCase
{
    use MockHelperTrait;

    const TEST_SERIALIZED_MAIL = '{serialized_mail}';

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $mailsSenderServiceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $msgConvertHelperMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $serializerMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $entityManagerMock;

    /** @var  MailsConsumerService */
    protected $mailsConsumerService;

    /** @var  Mail */
    protected $mail;

    /** @var  AMQPMessage */
    protected $message;

    /** @var array */
    protected $messageBody;

    /**
     * Set Up
     *
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->mail = new Mail();
        $this->createRabbitMQMessageMock();
        $this->mailsSenderServiceMock = $this->buildMock(MailsSenderService::class);
        $this->msgConvertHelperMock = $this->buildMock(MessageConversionHelper::class);
        $this->serializerMock = $this->buildMock(SerializerInterface::class);
        $this->entityManagerMock = $this->buildMock(EntityManager::class);

        $this->msgConvertHelperMock->expects($this->once())
            ->method('decodeMsg')
            ->with($this->equalTo($this->message->body))
            ->willReturn($this->messageBody);

        $this->mailsConsumerService = new MailsConsumerService(
            $this->mailsSenderServiceMock,
            $this->msgConvertHelperMock,
            $this->serializerMock,
            $this->entityManagerMock
        );
    }

    /**
     * Test execute method with send success result
     */
    public function testExecuteSentSuccess()
    {
        $this->serializerMock->expects($this->once())
            ->method('deserialize')
            ->with(
                $this->equalTo($this->messageBody->data),
                $this->equalTo(Mail::class),
                $this->equalTo('json'),
                $this->equalTo(DeserializationContext::create()->setGroups(['rabbitMQ']))
            )
            ->willReturn($this->mail);

        $this->mailsSenderServiceMock->expects($this->once())
            ->method('sendMail')
            ->with($this->equalTo($this->mail))
            ->willReturn(true);

        $this->expectOutputString(
            MailsConsumerService::TEXT_SENT,
            $this->mailsConsumerService->execute($this->message)
        );
    }

    /**
     * Test execute method with not sent result
     */
    public function testExecuteNotSent()
    {
        $this->serializerMock->expects($this->once())
            ->method('deserialize')
            ->with(
                $this->equalTo($this->messageBody->data),
                $this->equalTo(Mail::class),
                $this->equalTo('json'),
                $this->equalTo(DeserializationContext::create()->setGroups(['rabbitMQ']))
            )
            ->willReturn($this->mail);

        $this->mailsSenderServiceMock->expects($this->once())
            ->method('sendMail')
            ->with($this->equalTo($this->mail))
            ->willReturn(false);

        $this->expectOutputString(
            MailsConsumerService::TEXT_NOT_SENT,
            $this->mailsConsumerService->execute($this->message)
        );
    }

    /**
     * Test execute method with error result
     */
    public function testExecuteError()
    {
        $this->serializerMock->expects($this->once())
            ->method('deserialize')
            ->willReturn(null);

        $this->mailsSenderServiceMock->expects($this->never())
            ->method('sendMail');

        $this->expectOutputString(
            MailsConsumerService::TEXT_ERROR,
            $this->mailsConsumerService->execute($this->message)
        );
    }

    /**
     * Create RabbitMQ massage with Data given in messageBody
     */
    protected function createRabbitMQMessageMock()
    {
        $this->messageBody = new \StdClass();
        $this->messageBody->data = self::TEST_SERIALIZED_MAIL;

        $this->message = (new AMQPMessage())
            ->setBody(
                serialize(
                    json_encode($this->messageBody)
                )
            );
    }
}
