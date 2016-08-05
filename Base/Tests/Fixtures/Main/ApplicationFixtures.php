<?php

namespace Araneum\Base\Tests\Fixtures\Main;

use Araneum\Bundle\MainBundle\Entity\Application;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class ApplicationFixtures
 *
 * @package Araneum\Base\Tests\Fixtures\Main
 */
class ApplicationFixtures extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{
    const TEST_APP_NAME          = 'TestApplicationName';
    const TEST_APP_DOMAIN        = 'domain.com';
    const TEST_APP_ALIASES       = 'www.test.domain.com, www2.test.domain.com';
    const TEST_APP_PUBLIC        = true;
    const TEST_APP_ENABLED       = true;
    const TEST_APP_STATUS        = Application::STATUS_OK;
    const TEST_APP_TEMPLATE      = 'TestTemplate';
    const TEST_APP_APP_KEY       = '111111111111111';
    const TEST_APP_TEMP_NAME     = 'TestTempApplicationName';
    const TEST_APP_TEMP_DOMAIN   = 'domain.com';
    const TEST_APP_TEMP_ALIASES  = 'www.temp.domain.com, www2.temp.domain.com';
    const TEST_APP_TEMP_PUBLIC   = false;
    const TEST_APP_TEMP_ENABLED  = false;
    const TEST_APP_TEMP_STATUS   = Application::STATUS_OK;
    const TEST_APP_TEMP_TEMPLATE = 'TestTempTemplate';
    const TEST_APP_TEMP_APP_KEY  = '1111111111111111111';

    const TEST_INTERNAL_APP_NAME = 'TestInternalApplication';
    const TEST_INTERNAL_APP_KEY = '55555555555555';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $app = $manager
            ->getRepository('AraneumMainBundle:Application')
            ->findOneByName(self::TEST_APP_NAME);
        if (empty($app)) {
            $app = new Application();
            $app->setName(self::TEST_APP_NAME);
            $app->setDomain(self::TEST_APP_DOMAIN);
            $app->setAliases(self::TEST_APP_ALIASES);
            $app->setPublic(self::TEST_APP_PUBLIC);
            $app->setEnabled(self::TEST_APP_ENABLED);
            $app->setStatus(self::TEST_APP_STATUS);
            $app->setTemplate(self::TEST_APP_TEMPLATE);
            $app->setCluster($this->getReference('testCluster'));
            $app->setDb($this->getReference('testConnectionDb'));
            $app->setLocales(new ArrayCollection([$this->getReference('testLocale')]));
            $app->setComponents(new ArrayCollection([$this->getReference('testComponent')]));
            $app->setOwner($this->getReference('testOwner'));
            $app->setAppKey(self::TEST_APP_APP_KEY);
            $manager->persist($app);
            $manager->flush();
        }
        $this->addReference('testApplication', $app);

        $app = $manager
            ->getRepository('AraneumMainBundle:Application')
            ->findOneByName(self::TEST_APP_TEMP_NAME);

        if (empty($app)) {
            $app = new Application();
            $app->setName(self::TEST_APP_TEMP_NAME);
            $app->setDomain(self::TEST_APP_TEMP_DOMAIN);
            $app->setAliases(self::TEST_APP_TEMP_ALIASES);
            $app->setPublic(self::TEST_APP_TEMP_PUBLIC);
            $app->setEnabled(self::TEST_APP_TEMP_ENABLED);
            $app->setStatus(self::TEST_APP_TEMP_STATUS);
            $app->setTemplate(self::TEST_APP_TEMP_TEMPLATE);
            $app->setCluster($this->getReference('testCluster'));
            $app->setDb($this->getReference('testConnectionDb'));
            $app->setLocales(new ArrayCollection([$this->getReference('testLocale')]));
            $app->setComponents(new ArrayCollection([$this->getReference('testComponent')]));
            $app->setOwner($this->getReference('testOwner'));
            $app->setAppKey(self::TEST_APP_TEMP_APP_KEY);
            $manager->persist($app);
            $manager->flush();
        }

        $app = $manager
            ->getRepository('AraneumMainBundle:Application')
            ->findOneByName(self::TEST_INTERNAL_APP_NAME);

        if (empty($app)) {
            $app = new Application();
            $app->setName(self::TEST_INTERNAL_APP_NAME);
            $app->setDomain('testinternal.com');
            $app->setAliases('domainalias');
            $app->setPublic(true);
            $app->setEnabled(true);
            $app->setStatus(Application::STATUS_OK);
            $app->setTemplate('testTemplate');
            $app->setCluster($this->getReference('testCluster'));
            $app->setDb($this->getReference('testConnectionDb'));
            $app->setLocales(new ArrayCollection([$this->getReference('testLocale')]));
            $app->setComponents(new ArrayCollection([$this->getReference('testComponent')]));
            $app->setOwner($this->getReference('testOwner'));
            $app->setAppKey(self::TEST_INTERNAL_APP_KEY);
            $app->setType(Application::TYPE_INTERNAL);
            $manager->persist($app);
            $manager->flush();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getDependencies()
    {
        return [
            'Araneum\Base\Tests\Fixtures\Data\ApplicationData',
            'Araneum\Base\Tests\Fixtures\Main\RunnerFixtures',
            'Araneum\Base\Tests\Fixtures\Main\ClusterFixtures',
            'Araneum\Base\Tests\Fixtures\Main\ConnectionFixtures',
            'Araneum\Base\Tests\Fixtures\Main\ComponentFixtures',
        ];
    }
}
