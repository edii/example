<?php
namespace Araneum\Base\Traits;

use Araneum\Bundle\AgentBundle\Entity\Agent;
use Araneum\Bundle\MainBundle\Entity\Application;

trait ApiSendersTestTrait
{
    /**
     * @var Application
     */
    protected $application;

    /**
     * @var Agent
     */
    protected $agent;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;


    /**
     * Mock Entity Manager for api senders
     */
    protected function getMockEm()
    {
        $this->application = new Application();

        $this->agent = new Agent();

        $this->em = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em->expects($this->any())
            ->method('persist');

        $this->em->expects($this->any())
            ->method('flush');

        $this->em->expects($this->any())
            ->method('getReference')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue($this->application),
                    $this->returnValue($this->agent)
                )
            );
    }
}
