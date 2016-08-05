<?php

namespace Araneum\Base\Tests\Fixtures\Data;

use Araneum\Bundle\MainBundle\Entity\Component;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class ComponentData
 *
 * @package Araneum\Base\Tests\Fixtures\Data
 */
class ComponentData extends AbstractFixture implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $component = $manager->getRepository('AraneumMainBundle:Component')
            ->findOneByName('DefaultUltratradeComponent');
        if (empty($component)) {
            $component = new Component();
            $component->setName('DefaultUltratradeComponent');
            $component->setDescription('description');
            $component->setEnabled(true);
            $component->setDefault(true);
            $component->setOptions(
                [
                    'option1' => 'param1',
                ]
            );
            $manager->persist($component);
            $manager->flush();
        }
        $this->addReference('component', $component);
    }
}
