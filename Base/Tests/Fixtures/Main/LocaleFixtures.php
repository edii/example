<?php

namespace Araneum\Base\Tests\Fixtures\Main;

use Araneum\Bundle\MainBundle\Entity\Locale;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Knp\RadBundle\DataFixtures\AbstractFixture;

/**
 * Class LocaleFixtures
 *
 * @package Araneum\Base\Tests\Fixtures\Main
 */
class LocaleFixtures extends AbstractFixture implements FixtureInterface
{
    const TEST_LOC_NAME           = 'TestLocaleName';
    const TEST_LOC_LOCALE         = 'TestLoc';
    const TEST_LOC_ENABLED        = true;
    const TEST_LOC_ORIENT         = Locale::ORIENT_LFT_TO_RGT;
    const TEST_LOC_ENCOD          = 'UTF-8';
    const TEST_LOC_NAME_UPDATE    = 'TestLocalUpdate';
    const TEST_LOC_LOCALE_UPDATE  = 'ar_SA';
    const TEST_LOC_NAME_FILTER    = 'TestLocalFilter';
    const TEST_LOC_LOCALE_FILTER  = 'vi_VN';
    const TEST_LOC_ENABLED_FILTER = true;
    const TEST_LOC_ORIENT_FILTER  = Locale::ORIENT_LFT_TO_RGT;
    const TEST_LOC_ENCOD_FILTER   = 'UTF-8';
    const TEST_LOC_NAME_DELETE    = 'TestLocalDelete';
    const TEST_LOC_LOCALE_DELETE  = 'test';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $locale = $manager->getRepository('AraneumMainBundle:Locale')->findOneByName(self::TEST_LOC_NAME);
        if (empty($locale)) {
            $locale = new Locale();
            $locale->setName(self::TEST_LOC_NAME);
            $locale->setLocale(self::TEST_LOC_LOCALE);
            $locale->setEnabled(self::TEST_LOC_ENABLED);
            $locale->setOrientation(self::TEST_LOC_ORIENT);
            $locale->setEncoding(self::TEST_LOC_ENCOD);
            $manager->persist($locale);
        }
        $this->addReference('testLocale', $locale);

        $localeUpdate = $manager->getRepository('AraneumMainBundle:Locale')->findOneByName(self::TEST_LOC_NAME_UPDATE);
        if (empty($localeUpdate)) {
            $localeUpdate = new Locale();
            $localeUpdate->setName(self::TEST_LOC_NAME_UPDATE);
            $localeUpdate->setLocale(self::TEST_LOC_LOCALE_UPDATE);
            $localeUpdate->setEnabled(self::TEST_LOC_ENABLED);
            $localeUpdate->setOrientation(self::TEST_LOC_ORIENT);
            $localeUpdate->setEncoding(self::TEST_LOC_ENCOD);
            $manager->persist($localeUpdate);
        }

        $localeFilter = $manager->getRepository('AraneumMainBundle:Locale')->findOneByName(self::TEST_LOC_NAME_FILTER);
        if (empty($localeFilter)) {
            $localeFilter = new Locale();
            $localeFilter->setName(self::TEST_LOC_NAME_FILTER);
            $localeFilter->setLocale(self::TEST_LOC_LOCALE_FILTER);
            $localeFilter->setEnabled(self::TEST_LOC_ENABLED_FILTER);
            $localeFilter->setOrientation(self::TEST_LOC_ORIENT_FILTER);
            $localeFilter->setEncoding(self::TEST_LOC_ENCOD_FILTER);
            $localeFilter->setCreatedAt(new \DateTime('1980-1-1'));
            $manager->persist($localeFilter);
        }

        $localeDelete = $manager->getRepository('AraneumMainBundle:Locale')->findOneByName(self::TEST_LOC_NAME_DELETE);
        if (empty($localeDelete)) {
            $localeDelete = new Locale();
            $localeDelete->setName(self::TEST_LOC_NAME_DELETE);
            $localeDelete->setLocale(self::TEST_LOC_LOCALE_DELETE);
            $localeDelete->setEnabled(self::TEST_LOC_ENABLED);
            $localeDelete->setOrientation(self::TEST_LOC_ORIENT);
            $localeDelete->setEncoding(self::TEST_LOC_ENCOD);
            $manager->persist($localeDelete);
        }

        $localePack1 = $manager->getRepository('AraneumMainBundle:Locale')->findOneByName('LocalePack1');
        if (empty($localePack1)) {
            $localePack1 = new Locale();
            $localePack1->setName('LocalePack1');
            $localePack1->setLocale('mk_MK');
            $localePack1->setEnabled(true);
            $localePack1->setOrientation(Locale::ORIENT_LFT_TO_RGT);
            $localePack1->setEncoding(self::TEST_LOC_ENCOD);
            $manager->persist($localePack1);
        }

        $localePack2 = $manager->getRepository('AraneumMainBundle:Locale')->findOneByName('LocalePack2');
        if (empty($localePack2)) {
            $localePack2 = new Locale();
            $localePack2->setName('LocalePack2');
            $localePack2->setLocale('es_EU');
            $localePack2->setEnabled(true);
            $localePack2->setOrientation(Locale::ORIENT_LFT_TO_RGT);
            $localePack2->setEncoding(self::TEST_LOC_ENCOD);
            $manager->persist($localePack2);
        }
        $localePack3 = $manager->getRepository('AraneumMainBundle:Locale')->findOneByName('LocalePack3');
        if (empty($localePack3)) {
            $localePack3 = new Locale();
            $localePack3->setName('LocalePack3');
            $localePack3->setLocale('fr_FR');
            $localePack3->setEnabled(true);
            $localePack3->setOrientation(Locale::ORIENT_LFT_TO_RGT);
            $localePack3->setEncoding(self::TEST_LOC_ENCOD);
            $manager->persist($localePack3);
        }

        $localeDeletePack1 = $manager->getRepository('AraneumMainBundle:Locale')->findOneByName('LocaleDeletePack1');
        if (empty($localeDeletePack1)) {
            $localeDeletePack1 = new Locale();
            $localeDeletePack1->setName('LocaleDeletePack1');
            $localeDeletePack1->setLocale('mk_MK');
            $localeDeletePack1->setEnabled(true);
            $localeDeletePack1->setOrientation(Locale::ORIENT_LFT_TO_RGT);
            $localeDeletePack1->setEncoding(self::TEST_LOC_ENCOD);
            $manager->persist($localeDeletePack1);
        }

        $localeDeletePack2 = $manager->getRepository('AraneumMainBundle:Locale')->findOneByName('LocaleDeletePack2');
        if (empty($localeDeletePack2)) {
            $localeDeletePack2 = new Locale();
            $localeDeletePack2->setName('LocaleDeletePack2');
            $localeDeletePack2->setLocale('es_EU');
            $localeDeletePack2->setEnabled(true);
            $localeDeletePack2->setOrientation(Locale::ORIENT_LFT_TO_RGT);
            $localeDeletePack2->setEncoding(self::TEST_LOC_ENCOD);
            $manager->persist($localeDeletePack2);
        }
        $localeDeletePack3 = $manager->getRepository('AraneumMainBundle:Locale')->findOneByName('LocaleDeletePack3');
        if (empty($localeDeletePack3)) {
            $localeDeletePack3 = new Locale();
            $localeDeletePack3->setName('LocaleDeletePack3');
            $localeDeletePack3->setLocale('fr_FR');
            $localeDeletePack3->setEnabled(true);
            $localeDeletePack3->setOrientation(Locale::ORIENT_LFT_TO_RGT);
            $localeDeletePack3->setEncoding(self::TEST_LOC_ENCOD);
            $manager->persist($localeDeletePack3);
        }

        $manager->flush();
    }
}
