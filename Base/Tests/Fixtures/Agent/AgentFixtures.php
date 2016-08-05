<?php

namespace Araneum\Base\Tests\Fixtures\Agent;

use Araneum\Base\Tests\Fixtures\Main\ApplicationFixtures;
use Araneum\Bundle\AgentBundle\Entity\Agent;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AgentFixtures
 *
 * @package Araneum\Base\Tests\Fixtures\Agent
 */
class AgentFixtures extends AbstractFixture implements FixtureInterface, DependentFixtureInterface, ContainerAwareInterface
{
    private $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            'Araneum\Base\Tests\Fixtures\Main\ApplicationFixtures',
        ];
    }

    /**
     * Fixtures for agent
     *
     * @param ObjectManager $manager
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $application = $this->getReference('testApplication');
        $agent = $manager->getRepository('AraneumAgentBundle:Agent')->findOneBy(
            [
                'application' => $application,
                'type' => Agent::SPOTOPTION,
            ]
        );

        if (empty($agent)) {
            $agent = (new Agent())
                ->setType(Agent::SPOTOPTION)
                ->setApplication($application)
                ->setPrivateUrl('http://private.url')
                ->setPublicUrl('http://public.url')
                ->setApiUser('login')
                ->setApiPassword('password');

            $manager->persist($agent);
            $manager->flush();
        }
    }
}
