<?php

namespace Araneum\Base\Tests\Fixtures\Data;

use Araneum\Bundle\AgentBundle\Entity\Country;
use Araneum\Bundle\AgentBundle\Entity\Agent;
use Araneum\Bundle\MainBundle\Entity\Application;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RuntimeException;

/**
 * Class LoadCountryData
 *
 * @package Araneum\Base\Tests\Fixtures\Data
 */
class LoadCountryData extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
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
            'Araneum\Base\Tests\Fixtures\Data\LoadAgentData',
        ];
    }

    /**
     * Fixtures for country
     *
     * @param ObjectManager $manager
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var Application $application */
        $application = $this->getReference('application');
        if (!empty($application)) {
            $data = $this->container->get('araneum.agent.spotoption.service')->getCountries($application->getAppKey());
            if (!empty($data)) {
                foreach ($data as $countryData) {
                    $country = $manager->getRepository('AraneumAgentBundle:Country')->findOneBy(
                        ['name' => $countryData['iso']]
                    );
                    if (empty($country)) {
                        $country = new Country();
                        $country->setId($countryData['id']);
                        $country->setName($countryData['iso']);
                        $country->setTitle($countryData['name']);
                        $country->setPhoneCode(!empty($countryData['prefix']) ? $countryData['prefix'] : null);
                        $manager->persist($country);
                    }
                }
                $manager->flush();
            } else {
                throw new RuntimeException('Failed to parse countries');
            }
        }
    }
}
