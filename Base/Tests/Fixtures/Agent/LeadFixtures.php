<?php

namespace Araneum\Base\Tests\Fixtures\Agent;

use Araneum\Bundle\AgentBundle\Entity\Lead;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class LeadFixtures
 *
 * @package Araneum\Base\Tests\Fixtures\Agent
 */
class LeadFixtures extends AbstractFixture implements FixtureInterface, DependentFixtureInterface
{
    const LEAD_FST_EMAIL = 'ivan.ivanovich@lead.com';
    const LEAD_FST_PHONE = '380501234567';

    const LEAD_SND_EMAIL = 'petrt.petrovich@lead.com';
    const LEAD_SND_PHONE = '380507654321';

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $repository = $manager->getRepository('AraneumAgentBundle:Lead');

        if (empty($repository->findByEmail(self::LEAD_FST_EMAIL))) {
            $manager->persist(
                (new Lead())
                    ->setFirstName('Ivan')
                    ->setLastName('Ivanovich')
                    ->setCountry(rand(1, 239))
                    ->setEmail(self::LEAD_FST_EMAIL)
                    ->setPhone(self::LEAD_FST_PHONE)
                    ->setApplication($this->getReference('testApplication'))
            );
        }

        if (empty($repository->findByEmail(self::LEAD_SND_EMAIL))) {
            $manager->persist(
                (new Lead())
                    ->setFirstName('Petr')
                    ->setLastName('Petrovich')
                    ->setCountry(rand(1, 239))
                    ->setEmail(self::LEAD_SND_EMAIL)
                    ->setPhone(self::LEAD_SND_PHONE)
                    ->setApplication($this->getReference('testApplication'))
            );
        }

        $manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on
     *
     * @return array
     */
    public function getDependencies()
    {
        return ['Araneum\Base\Tests\Fixtures\Main\ApplicationFixtures'];
    }
}
