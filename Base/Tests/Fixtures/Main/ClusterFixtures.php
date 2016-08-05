<?php

namespace Araneum\Base\Tests\Fixtures\Main;

use Araneum\Bundle\MainBundle\Entity\Cluster;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class ClusterFixtures
 *
 * @package Araneum\Base\Tests\Fixtures\Main
 */
class ClusterFixtures extends AbstractFixture implements FixtureInterface
{
    const TEST_CLU_NAME    = 'TestClusterName';
    const TEST_CLU_TYPE    = 1;
    const TEST_CLU_ENABLED = true;
    const TEST_CLU_STATUS  = Cluster::STATUS_OK;

    const TEST_TEMP_CLU_NAME = 'TestTempName';
    const DELETE_CLU_NAME    = 'DeleteClusterName';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $cluster = $manager->getRepository('AraneumMainBundle:Cluster')->findOneByName(self::TEST_CLU_NAME);
        if (empty($cluster)) {
            $cluster = new Cluster();
            $cluster->setName(self::TEST_CLU_NAME);
            $cluster->setType(self::TEST_CLU_TYPE);
            $cluster->setEnabled(self::TEST_CLU_ENABLED);
            $cluster->setStatus(self::TEST_CLU_STATUS);
            $manager->persist($cluster);
            $manager->flush();
        }
        $this->addReference('testCluster', $cluster);

        $clusterTmp = $manager->getRepository('AraneumMainBundle:Cluster')->findOneByName(self::TEST_TEMP_CLU_NAME);
        if (empty($clusterTmp)) {
            $clusterTmp = new Cluster();
            $clusterTmp->setName(self::TEST_TEMP_CLU_NAME);
            $clusterTmp->setType(self::TEST_CLU_TYPE);
            $clusterTmp->setEnabled(self::TEST_CLU_ENABLED);
            $clusterTmp->setStatus(self::TEST_CLU_STATUS);
            $manager->persist($clusterTmp);
            $manager->flush();
        }

        $deleteCluster = $manager->getRepository('AraneumMainBundle:Cluster')->findOneByName(self::DELETE_CLU_NAME);
        if (empty($deleteCluster)) {
            $deleteCluster = new Cluster();
            $deleteCluster->setName(self::DELETE_CLU_NAME);
            $deleteCluster->setType(self::TEST_CLU_TYPE);
            $deleteCluster->setEnabled(self::TEST_CLU_ENABLED);
            $deleteCluster->setStatus(self::TEST_CLU_STATUS);
            $manager->persist($deleteCluster);
            $manager->flush();
        }
    }
}
