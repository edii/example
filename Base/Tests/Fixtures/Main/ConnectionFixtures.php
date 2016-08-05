<?php

namespace Araneum\Base\Tests\Fixtures\Main;

use Araneum\Bundle\MainBundle\Entity\Connection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class ConnectionFixtures
 *
 * @package Araneum\Base\Tests\Fixtures\Main
 */
class ConnectionFixtures extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{
    const TEST_CONN_POSTGRESS_TYPE     = 1;
    const TEST_CONN_NGINX_TYPE   = 2;
    const TEST_CONN_NAME        = 'TestConnection';
    const TEST_CONN_POSTGRESS_NAME     = 'TestDbConnection';
    const TEST_CONN_NGINX        = '192.168.1.200';
    const TEST_CONN_PORT        = 1234;
    const TEST_CONN_POSTGRESS_PORT     = 4321;
    const TEST_CONN_ENABLED     = true;
    const TEST_CONN_USERNAME    = 'TestConnectionUserName';
    const TEST_CONN_POSTGRESS_USERNAME = 'TestDbConnectionUserName';
    const TEST_CONN_PASS        = 'TestConnectionPassword';
    const TEST_CONN_POSTGRESS_PASS     = 'TestDbConnectionPassword';
    const TEST_CONN_FREE_NAME   = 'TestConnectionFreeName';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $connectionHost = $manager
            ->getRepository('AraneumMainBundle:Connection')
            ->findOneByName(self::TEST_CONN_NAME);

        if (empty($connectionHost)) {
            $connectionHost = new Connection();
            $connectionHost->setType(self::TEST_CONN_NGINX_TYPE);
            $connectionHost->setName(self::TEST_CONN_NAME);
            $connectionHost->setHost(self::TEST_CONN_NGINX);
            $connectionHost->setPort(self::TEST_CONN_PORT);
            $connectionHost->setEnabled(self::TEST_CONN_ENABLED);
            $connectionHost->setRunner($this->getReference('testRunner'));
            $connectionHost->setUserName(self::TEST_CONN_USERNAME);
            $connectionHost->setPassword(self::TEST_CONN_PASS);
            $manager->persist($connectionHost);
            $manager->flush();
        }

        $connectionDb = $manager
            ->getRepository('AraneumMainBundle:Connection')
            ->findOneByName(self::TEST_CONN_POSTGRESS_NAME);

        if (empty($connectionDb)) {
            $connectionDb = new Connection();
            $connectionDb->setType(self::TEST_CONN_POSTGRESS_TYPE);
            $connectionDb->setName(self::TEST_CONN_POSTGRESS_NAME);
            $connectionDb->setHost(self::TEST_CONN_NGINX);
            $connectionDb->setPort(self::TEST_CONN_POSTGRESS_PORT);
            $connectionDb->setEnabled(self::TEST_CONN_ENABLED);
            $connectionDb->setRunner($this->getReference('testRunner'));
            $connectionDb->setUserName(self::TEST_CONN_POSTGRESS_USERNAME);
            $connectionDb->setPassword(self::TEST_CONN_POSTGRESS_PASS);
            $manager->persist($connectionDb);
            $manager->flush();
        }

        $connectionFree = $manager
            ->getRepository('AraneumMainBundle:Connection')
            ->findOneByName(self::TEST_CONN_FREE_NAME);

        if (empty($connectionFree)) {
            $connectionFree = new Connection();
            $connectionFree->setType(self::TEST_CONN_NGINX_TYPE);
            $connectionFree->setName(self::TEST_CONN_FREE_NAME);
            $connectionFree->setHost(self::TEST_CONN_NGINX);
            $connectionFree->setPort(self::TEST_CONN_PORT);
            $connectionFree->setEnabled(self::TEST_CONN_ENABLED);
            $connectionFree->setRunner($this->getReference('testRunner'));
            $connectionFree->setUserName(self::TEST_CONN_USERNAME);
            $connectionFree->setPassword(self::TEST_CONN_PASS);
            $manager->persist($connectionFree);
            $manager->flush();
        }

        $this->addReference('testConnectionHost', $connectionHost);
        $this->addReference('testConnectionDb', $connectionDb);
    }

    /**
     * Dependencies
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            'Araneum\Base\Tests\Fixtures\Main\RunnerFixtures',
        ];
    }
}
