<?php

namespace Araneum\Base\Tests\Controller;

use Araneum\Base\Traits\MockHelperTrait;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Araneum\Base\Service\RabbitMQ\ProducerService;

/**
 * Class BaseController
 *
 * @package Araneum\Base\Tests\Controller
 */
class BaseController extends WebTestCase
{
    use MockHelperTrait;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $guzzleClientMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $guzzleHttpRequestMock;

    /**
     * @var ProducerService
     */
    protected $rabbitmqProducerMock;

    /**
     * Mock rabbitmqProducer and set to container.
     *
     * @param Client $client
     * @param string $serviceName
     */
    public function mockRabbitmqProducer(Client $client, $serviceName)
    {
        $this->rabbitmqProducerMock = $this->buildMock(ProducerService::class);

        $client->getContainer()->set($serviceName, $this->rabbitmqProducerMock);
    }

    /**
     * Return admin authorized client
     *
     * @param string      $authLogin
     * @param string|null $firewallName
     * @return Client
     */
    protected static function createAdminAuthorizedClient($authLogin = 'admin', $firewallName = null)
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $session = $container->get('session');
        /** @var $userManager \FOS\UserBundle\Doctrine\UserManager */
        $userManager = $container->get('fos_user.user_manager');
        /** @var $loginManager \FOS\UserBundle\Security\LoginManager */
        $loginManager = $container->get('fos_user.security.login_manager');
        if ($firewallName === null) {
            $firewallName = $container->getParameter('fos_user.firewall_name');
        }

        $user = $userManager->findUserBy(['username' => $authLogin]);
        $loginManager->loginUser($firewallName, $user);

        $session->set(
            '_security_'.$firewallName,
            serialize($container->get('security.token_storage')->getToken())
        );

        $session->save();
        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        return $client;
    }

    /**
     * Asserting Structures of Objects are Equal
     *
     * @param \stdClass $expected
     * @param \stdClass $actual
     */
    protected function assertObjectsStructuresEquals(\stdClass $expected, \stdClass $actual)
    {
        foreach ($expected as $key => $value) {
            $this->assertObjectHasAttribute($key, $actual, json_encode($actual));

            if (is_object($value)) {
                $this->assertObjectsStructuresEquals($value, $actual->{$key});
            }
        }
    }

    /**
     * Creates a Client.
     * If authLogin not empty, client will be create for login user.
     * If firewallName not empty than user will be login by this firewall.
     * If disableReboot is true reboot between requests is off.
     *
     * Mock guzzle with request and set mock guzzle client to container.
     * Mock rabbitmqProducer and set to container.
     *
     * @param string      $authLogin
     * @param string|null $firewallName
     * @param array       $options An array of options to pass to the createKernel class
     * @param array       $server  An array of server parameters
     * @param bool|false  $disableReboot
     * @return Client
     */
    protected function createClientWithMockServices(
        $authLogin = null,
        $firewallName = null,
        array $options = [],
        array $server = [],
        $disableReboot = false
    ) {
        $client = $this->createClientByParams($options, $server, $authLogin, $firewallName);
        if ($disableReboot) {
            $client->disableReboot();
        }

        $this->mockGuzzleClientWithRequest($client, 'guzzle.client');
        $this->mockRabbitmqProducer($client, 'araneum.base.rabbitmq.producer.spot');
        $this->mockRabbitmqProducer($client, 'araneum.base.rabbitmq.producer.spot_lead');
        $this->mockRabbitmqProducer($client, 'araneum.base.rabbitmq.producer.spot_login');
        $this->mockRabbitmqProducer($client, 'araneum.base.rabbitmq.producer.spot_customer');

        return $client;
    }

    /**
     * Creates a Client by params.
     * If authLogin not empty, client will be create for login user.
     * If firewallName not empty than user will be login by this firewall.
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     * @param null  $authLogin
     * @param null  $firewallName
     * @return mixed
     */
    private function createClientByParams(
        array $options = [],
        array $server = [],
        $authLogin = null,
        $firewallName = null
    ) {
        $method = 'createClient';
        $parameters = [
            $options,
            $server,
        ];
        if (!empty($authLogin)) {
            $method = 'createAdminAuthorizedClient';
            $parameters = [
                $authLogin,
                $firewallName,
            ];
        }

        return forward_static_call_array(
            [
                $this,
                $method,
            ],
            $parameters
        );
    }

    /**
     * Mock guzzle with request and set mock guzzle client to container.
     *
     * @param Client $client
     * @param string $serviceName
     */
    private function mockGuzzleClientWithRequest(Client $client, $serviceName)
    {
        $this->guzzleClientMock = $this
            ->getMockBuilder('\Guzzle\Service\ClientInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->guzzleHttpRequestMock = $this
            ->getMockBuilder('\Guzzle\Http\Message\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $client->getContainer()->set($serviceName, $this->guzzleClientMock);
    }
}
