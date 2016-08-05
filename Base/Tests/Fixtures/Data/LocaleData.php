<?php

namespace Araneum\Base\Tests\Fixtures\Data;

use Araneum\Bundle\MainBundle\Entity\Locale;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

/**
 * Class LocaleData
 *
 * @package Araneum\Base\Tests\Fixtures\Data
 */
class LocaleData extends AbstractFixture implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $localeEn = $manager->getRepository('AraneumMainBundle:Locale')->findOneByLocale('en_US');
        if (empty($localeEn)) {
            $localeEn = new Locale();
            $localeEn->setName('en');
            $localeEn->setLocale('en_US');
            $localeEn->setOrientation(Locale::ORIENT_LFT_TO_RGT);
            $localeEn->setEncoding('UTF-8');
            $manager->persist($localeEn);
            $manager->flush();
        }
        $this->addReference('locale', $localeEn);
    }
}
