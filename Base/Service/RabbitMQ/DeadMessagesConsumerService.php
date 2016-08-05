<?php

namespace Araneum\Base\Service\RabbitMQ;

use Araneum\Bundle\UserBundle\Entity\User;
use Guzzle\Service;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Araneum\Bundle\MainBundle\Entity\RabbitMessage;
use Symfony\Component\Security\Acl\Exception\Exception;

/**
 * Class SpotConsumerService
 *
 * @package Araneum\Base\Service\RabbitMQ
 */
class DeadMessagesConsumerService implements ConsumerInterface
{
    /**
     * Set content type
     *
     * @var array
     */
    protected static $contentTypes = [
        'html' => 'text/html',
        'plain' => 'text/plain',
    ];

    /**
     * Set charset to sendmailer
     *
     * @var string
     */
    const CHARSET = 'UTF-8';

    /**
     * @var string
     */
    const MAIL_FROM = 'araneum.dev@gmail.com';

    /**
     * @var MessageConversionHelper
     */
    private $msgConvertHelper;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Consumer constructor.
     *
     * @param MessageConversionHelper $msgConvertHelper
     * @param ContainerInterface      $container
     */
    public function __construct(
        MessageConversionHelper $msgConvertHelper,
        ContainerInterface $container
    ) {
        $this->msgConvertHelper = $msgConvertHelper;
        $this->container = $container;
    }

    /**
     * Receive message
     *
     * @param AMQPMessage $message
     * @return string
     */
    public function execute(AMQPMessage $message)
    {
        $manager = $this->container->get('doctrine.orm.entity_manager');
        $data = json_decode($message->body);
        $message = (new RabbitMessage())
            ->setRoutingKey($data->routingKey)
            ->setMessage(json_encode($data->data))
            ->setEmailStatus(true)
        ;

        $users = $manager->getRepository('AraneumUserBundle:User')->getAllowedToReceiveSystemEmails();

        $twig = $this->container->get('templating');
        $template = $twig->render(
            '::mail.dead.messages.html.twig',
            [
                'data' => $data,
            ]
        );

        try {
            $mailer = $this->container->get('mailer');
            foreach ($users as $user) {
                $email = \Swift_Message::newInstance()
                    ->setSubject('RabbitMQ DeadLetter')
                    ->setFrom(self::MAIL_FROM)
                    ->setTo($user->getEmail())
                    ->setCharset(self::CHARSET)
                    ->setContentType(self::$contentTypes['plain'])
                    ->setBody($template, self::$contentTypes['html']);
                $mailer->send($email);
            }
            $message->setEmailStatus(true);
        } catch (Exception $e) {
            $message->setEmailStatus(false);
        }
        $manager->persist($message);
        $manager->flush();
    }
}
