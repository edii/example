<?php

namespace Araneum\Base\Tests\Fixtures\Data;

use Araneum\Bundle\MainBundle\Entity\Cluster;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class ClusterData
 *
 * @package Araneum\Base\Tests\Fixtures\Data
 */
class ClusterData extends AbstractFixture implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $cluster = $manager->getRepository('AraneumMainBundle:Cluster')->findOneByName('DefaultUltratradeCluster');

        if (empty($cluster)) {
            $cluster = new Cluster();
            $cluster->setName('DefaultUltratradeCluster');
            $cluster->setType(1);
            $cluster->setEnabled(true);
            $cluster->setStatus(Cluster::STATUS_OK);
            $manager->persist($cluster);
            $manager->flush();
        }

        $this->addReference('cluster', $cluster);
    }
}
