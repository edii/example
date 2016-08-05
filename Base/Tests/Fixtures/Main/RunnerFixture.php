<?php

namespace Araneum\Base\Tests\Fixtures\Main;

use Araneum\Bundle\MainBundle\Entity\Runner;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class RunnerFixtures
 *
 * @package Araneum\Base\Tests\Fixtures\Main
 */
class RunnerFixtures extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{
    const TEST_RUNNER_NAME    = 'TestRunnerName';
    const TEST_RUNNER_DOMAIN  = 'TestRunnerDomain.com';
    const TEST_RUNNER_TYPE    = 1;
    const TEST_RUNNER_ENABLED = true;
    const TEST_RUNNER_STATUS  = 1;

    const TEST_TEMP_RUNNER_NAME = 'TestTempName';
    const DELETE_RUNNER_NAME    = 'DeleterunnerName';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $runner = $manager->getRepository('AraneumMainBundle:Runner')->findOneByName(self::TEST_RUNNER_NAME);
        if (empty($runner)) {
            $runner = new Runner();
            $runner->setName(self::TEST_RUNNER_NAME);
            $runner->setCluster($this->getReference('testCluster'));
            $runner->setType(self::TEST_RUNNER_TYPE);
            $runner->setEnabled(self::TEST_RUNNER_ENABLED);
            $runner->setStatus(self::TEST_RUNNER_STATUS);
            $runner->setDomain(self::TEST_RUNNER_DOMAIN);
            $manager->persist($runner);
            $manager->flush();
        }


        $runnerTmp = $manager->getRepository('AraneumMainBundle:Runner')->findOneByName(self::TEST_TEMP_RUNNER_NAME);
        if (empty($runnerTmp)) {
            $runnerTmp = new runner();
            $runnerTmp->setName(self::TEST_TEMP_RUNNER_NAME);
            $runnerTmp->setCluster($this->getReference('testCluster'));
            $runnerTmp->setType(self::TEST_RUNNER_TYPE);
            $runnerTmp->setEnabled(self::TEST_RUNNER_ENABLED);
            $runnerTmp->setStatus(self::TEST_RUNNER_STATUS);
            $runnerTmp->setDomain(self::TEST_RUNNER_DOMAIN);
            $manager->persist($runnerTmp);
            $manager->flush();
        }

        $deleteRunner = $manager->getRepository('AraneumMainBundle:Runner')->findOneByName(self::DELETE_RUNNER_NAME);
        if (empty($deleteRunner)) {
            $deleteRunner = new runner();
            $deleteRunner->setName(self::DELETE_RUNNER_NAME);
            $deleteRunner->setCluster($this->getReference('testCluster'));
            $deleteRunner->setType(self::TEST_RUNNER_TYPE);
            $deleteRunner->setEnabled(self::TEST_RUNNER_ENABLED);
            $deleteRunner->setStatus(self::TEST_RUNNER_STATUS);
            $deleteRunner->setDomain(self::TEST_RUNNER_DOMAIN);
            $manager->persist($deleteRunner);
            $manager->flush();
        }
        $this->addReference('testRunner', $runner);
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            'Araneum\Base\Tests\Fixtures\Main\ClusterFixtures',
        ];
    }
}
