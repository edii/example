<?php

namespace Araneum\Base\Tests\Fixtures\Data;

use Doctrine\Common\Collections\ArrayCollection;
use Araneum\Bundle\MainBundle\Entity\Application;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class ApplicationData
 *
 * @package Araneum\Base\Tests\Fixtures\Data
 */
class ApplicationData extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->setUltratrade($manager);
        $this->setIxoption($manager);
        $this->setToroption($manager);
        $this->setTradersbot($manager);
        $this->setBenjaminRanford($manager);
        $this->setApisSystem($manager);
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            'Araneum\Base\Tests\Fixtures\Data\LocaleData',
            'Araneum\Base\Tests\Fixtures\Data\ConnectionData',
            'Araneum\Base\Tests\Fixtures\Data\ClusterData',
            'Araneum\Bundle\UserBundle\DataFixtures\ORM\UserData',
            'Araneum\Base\Tests\Fixtures\Data\ComponentData',
        ];
    }

    /**
     * set Ultratrade add fixture
     *
     * @param ObjectManager $manager
     */
    private function setUltratrade(ObjectManager $manager)
    {
        $app = $manager
            ->getRepository('AraneumMainBundle:Application')
            ->findOneByName('Ultratrade');

        if (empty($app)) {
            $app = new Application();
            $app->setName('Ultratrade');
            $app->setDomain('ultratrade.office.dev');
            $app->setPublic(true);
            $app->setEnabled(true);
            $app->setStatus(Application::STATUS_OK);
            $app->setTemplate('DefaultTemplate');
            $app->setCluster($this->getReference('cluster'));
            $app->setDb($this->getReference('connectionDb'));
            $app->setLocales(new ArrayCollection([$this->getReference('locale')]));
            $app->setOwner($this->getReference('userAdmin'));
            $app->setComponents(new ArrayCollection([$this->getReference('component')]));
            $app->setAppKey('f2481f3c3d2d7e9d9669e1ec3a3e01d30785270c563b60a417de93.70304637');
            $manager->persist($app);
            $manager->flush();
        }
        $this->addReference('application', $app);
    }

    /**
     * set Ixoption add fixture
     *
     * @param ObjectManager $manager
     */
    private function setIxoption(ObjectManager $manager)
    {
        $app = $manager
            ->getRepository('AraneumMainBundle:Application')
            ->findOneByName('ixoption');

        if (empty($app)) {
            $app = new Application();
            $app->setName('ixoption');
            $app->setDomain('ixoption.com');
            $app->setPublic(true);
            $app->setEnabled(true);
            $app->setStatus(Application::STATUS_OK);
            $app->setTemplate('DefaultTemplate');
            $app->setCluster($this->getReference('cluster'));
            $app->setDb($this->getReference('connectionDb'));
            $app->setLocales(new ArrayCollection([$this->getReference('locale')]));
            $app->setOwner($this->getReference('userAdmin'));
            $app->setAppKey('cb678b70df4d0e2ad5b7eb8688a7df186cc49cf056af25ff047a91.81106394');
            $manager->persist($app);
            $manager->flush();
        }
        $this->addReference('appIxoption', $app);
    }

    /**
     * set Toroption add fixture
     *
     * @param ObjectManager $manager
     */
    private function setToroption(ObjectManager $manager)
    {
        $app = $manager
            ->getRepository('AraneumMainBundle:Application')
            ->findOneByName('toroption');

        if (empty($app)) {
            $app = new Application();
            $app->setName('toroption');
            $app->setDomain('toroption.com');
            $app->setPublic(true);
            $app->setEnabled(true);
            $app->setStatus(Application::STATUS_OK);
            $app->setTemplate('DefaultTemplate');
            $app->setCluster($this->getReference('cluster'));
            $app->setDb($this->getReference('connectionDb'));
            $app->setLocales(new ArrayCollection([$this->getReference('locale')]));
            $app->setOwner($this->getReference('userAdmin'));
            $app->setAppKey('fa5a7ad786dc76e84b95079a47fe65f9a8f2df0ed125b05aa023c0.83609407');
            $manager->persist($app);
            $manager->flush();
        }
        $this->addReference('appToroption', $app);
    }

    /**
     * set Traderesbot add fixture
     *
     * @param ObjectManager $manager
     */
    private function setTradersbot(ObjectManager $manager)
    {
        $app = $manager
            ->getRepository('AraneumMainBundle:Application')
            ->findOneByName('Tradersbot');

        if (empty($app)) {
            $app = new Application();
            $app->setName('Tradersbot');
            $app->setDomain('tradersbot.office.dev');
            $app->setPublic(true);
            $app->setEnabled(true);
            $app->setStatus(Application::STATUS_OK);
            $app->setTemplate('DefaultTemplate');
            $app->setCluster($this->getReference('cluster'));
            $app->setDb($this->getReference('connectionDb'));
            $app->setLocales(new ArrayCollection([$this->getReference('locale')]));
            $app->setOwner($this->getReference('userAdmin'));
            $app->setAppKey('dc2e413437737725eab936a0d6c9532e507cec7156cc63f1bbd4e1.01384540');
            $manager->persist($app);
            $manager->flush();
        }
        $this->addReference('appTradersbot', $app);
    }

    /**
     * set BenjaminRanford add fixture
     *
     * @param ObjectManager $manager
     */
    private function setBenjaminRanford(ObjectManager $manager)
    {
        $app = $manager
            ->getRepository('AraneumMainBundle:Application')
            ->findOneByName('BenjaminRanford');

        if (empty($app)) {
            $app = new Application();
            $app->setName('BenjaminRanford');
            $app->setDomain('benjaminranford.com');
            $app->setPublic(true);
            $app->setEnabled(true);
            $app->setStatus(Application::STATUS_OK);
            $app->setTemplate('DefaultTemplate');
            $app->setCluster($this->getReference('cluster'));
            $app->setDb($this->getReference('connectionDb'));
            $app->setLocales(new ArrayCollection([$this->getReference('locale')]));
            $app->setOwner($this->getReference('userAdmin'));
            $app->setAppKey('0e1de4a209240e4e453aa048e84b5130ca9a749756f8d682de13e4.79774363');
            $manager->persist($app);
            $manager->flush();
        }
        $this->addReference('appBenjaminRanford', $app);
    }

    /**
     * set ApisSystem add fixture
     *
     * @param ObjectManager $manager
     */
    private function setApisSystem(ObjectManager $manager)
    {
        $app = $manager
            ->getRepository('AraneumMainBundle:Application')
            ->findOneByName('apissystem');

        if (empty($app)) {
            $app = new Application();
            $app->setName('apissystem');
            $app->setDomain('apissystem.com');
            $app->setPublic(true);
            $app->setEnabled(true);
            $app->setStatus(Application::STATUS_OK);
            $app->setTemplate('DefaultTemplate');
            $app->setCluster($this->getReference('cluster'));
            $app->setDb($this->getReference('connectionDb'));
            $app->setLocales(new ArrayCollection([$this->getReference('locale')]));
            $app->setOwner($this->getReference('userAdmin'));
            $app->setAppKey('07a3e4a209240e4e453aa048e84b5130ca9a749756f8d682de12a7.08174834');
            $app->setType(Application::TYPE_INTERNAL);
            $manager->persist($app);
            $manager->flush();
        }
        $this->addReference('appApisSystem', $app);
    }
}
