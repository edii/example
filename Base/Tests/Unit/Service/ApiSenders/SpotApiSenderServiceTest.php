<?php

namespace Araneum\Base\Tests\Unit\Service\ApiSenders;

use Araneum\Base\Service\ApiSenders\SpotApiSenderService;
use Araneum\Base\Traits\ApiSendersTestTrait;
use Araneum\Base\Traits\MockHelperTrait;
use Guzzle\Http\Message\EntityEnclosingRequestInterface;
use Guzzle\Http\Message\Response;
use Guzzle\Service\ClientInterface;

/**
 * Class SpotApiSenderServiceTest
 *
 * @package Araneum\Base\Service\Spot
 */
class SpotApiSenderServiceTest extends \PHPUnit_Framework_TestCase
{
    use ApiSendersTestTrait;
    use MockHelperTrait;

    const TEST_LOGIN_URL = '/login/test';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $guzzleMock;
    /**
     * @var SpotApiSenderService
     */
    protected $spotApiSenderService;

    protected $requestData = [
        'key' => 'value',
        'key2' => 'value',
        'key3' => 'value',
    ];
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * Test getErrors with normal data
     */
    public function testGetErrorsNormal()
    {
        $this->responseMock
            ->expects($this->once())
            ->method('json')
            ->will(
                $this->returnValue(
                    [
                        'status' => [
                            'connection_status' => 'successful',
                            'operation_status' => 'successful',
                        ],
                    ]
                )
            );

        $this->assertNull($this->spotApiSenderService->getErrors($this->responseMock));
    }

    /**
     * Test getErrors with normal data
     */
    public function testGetErrorsBad()
    {
        $this->responseMock
            ->expects($this->once())
            ->method('json')
            ->will(
                $this->returnValue(
                    [
                        'status' => [
                            'connection_status' => 'successful',
                            'operation_status' => 'fail',
                            'errors' => 'errors message',
                        ],
                    ]
                )
            );

        $this->assertEquals(json_encode('errors message'), $this->spotApiSenderService->getErrors($this->responseMock));
    }

    /**
     * Test getErrors with Exception
     *
     * @expectedException \BadMethodCallException
     */
    public function testGetErrorsException()
    {
        $this->responseMock
            ->expects($this->once())
            ->method('json')
            ->will($this->returnValue(['not valid response']));
        $this->spotApiSenderService->getErrors($this->responseMock);
    }

    /**
     *  Test normal work of method
     */
    public function testSendNormalData()
    {
        $this->requestMock->expects($this->once())->method('send');
        $spotCredential = [
            'credentials' => [
                'apiPrivateUrl' => 'http://spotUrl.com',
                'apiUser' => 'spotUserName',
                'apiPassword' => 'spotPassword',
            ],
            'log' => [
                'applicationId' => 1,
                'agentId' => 1,
            ],
        ];
        $this->guzzleMock->expects($this->once())
            ->method('setBaseUrl')
            ->will($this->returnValue('spotUrl'));
        $this->guzzleMock->expects($this->once())
            ->method('post')
            ->with(
                null,
                null,
                $this->equalTo(
                    array_merge(
                        $this->requestData,
                        [
                            'api_username' => $spotCredential['credentials']['apiUser'],
                            'api_password' => $spotCredential['credentials']['apiPassword'],
                            'jsonResponse' => 'true',
                        ]
                    )
                )
            )
            ->will($this->returnValue($this->requestMock));

        $this->spotApiSenderService->send(
            $this->requestData,
            $spotCredential
        );
    }

    /**
     * Test method with bad spotOption data must throw exception
     */
    public function testSendBadDataException()
    {
        $spotCredential = [
            'credentials' => [
                'url' => 'notValid',
                'userName' => 'spotUserName',
                'password' => 'spotPassword',
            ],
            'log' => [
                'applicationId' => 1,
                'agentId' => 1,
            ],
        ];

        $result = $this->spotApiSenderService->send(
            $this->requestData,
            $spotCredential
        );
        $this->assertInstanceOf(
            "BadMethodCallException",
            $result,
            "Test method with bad spotOption data must throw exception"
        );
    }

    /**
     * Set Up
     */
    protected function setUp()
    {
        $this->guzzleMock = $this->buildMock(ClientInterface::class);
        $this->responseMock = $this->buildMock(Response::class);
        $this->requestMock = $this->buildMock(EntityEnclosingRequestInterface::class);
        $this->getMockEm();
        $this->spotApiSenderService = new SpotApiSenderService(
            $this->guzzleMock,
            $this->em,
            true,
            self::TEST_LOGIN_URL
        );
    }
}
