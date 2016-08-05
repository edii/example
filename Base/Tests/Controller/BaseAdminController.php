<?php

namespace Araneum\Base\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;

/**
 * Class BaseAdminController.
 * Implements most of methods to test datatables in admin panel
 *
 * @package Araneum\Base\Tests\Controller
 */
class BaseAdminController extends BaseController
{
    /**
     * @var Client
     */
    private $client;

    /** @var Router */
    private $router;

    /** @var string */
    protected $initActionUrl;

    /** @var string */
    protected $gridActionUrl;

    /** @var int */
    protected $requestedItemsAmount = 1;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->client = self::createAdminAuthorizedClient('admin');

        /**
         * @var Router router
         */
        $this->router = $this->client->getContainer()->get('router');
    }

    /**
     * Test Initialize Action
     *
     * @runInSeparateProcess
     */
    public function testInitAction()
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->router->generate($this->initActionUrl),
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        /**
         * @var Response $response
         */
        $response = $this->client->getResponse();

        $this->assertTrue($response->isSuccessful());

        $this->assertObjectsStructuresEquals(
            (object) [
                'grid' => [
                    'columns' => [],
                ],
            ],
            json_decode($response->getContent())
        );
    }

    /**
     * Test datatable
     *
     * @runInSeparateProcess
     */
    public function testDatatableAction()
    {
        $this->client->request(
            Request::METHOD_POST,
            $this->router->generate($this->gridActionUrl),
            [
                'iDisplayStart' => 0,
                'iDisplayLength' => $this->requestedItemsAmount,
            ],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );

        /**
         * @var Response $response
         */
        $response = $this->client->getResponse();
        $this->assertTrue($response->isSuccessful());
        $responseObject = json_decode($response->getContent());
        $this->assertObjectsStructuresEquals(
            (object) [
                'aaData' => [],
                'iTotalDisplayRecords' => rand(),
                'iTotalRecords' => rand(),
                'sEcho' => rand(),
            ],
            $responseObject
        );
        $this->assertTrue(count($responseObject->aaData) <= $this->requestedItemsAmount);
    }
}
