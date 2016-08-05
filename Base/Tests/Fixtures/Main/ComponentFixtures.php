<?php

namespace Araneum\Base\Tests\Fixtures\Main;

use Araneum\Bundle\MainBundle\Entity\Component;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class ComponentFixtures
 *
 * @package Araneum\Base\Tests\Fixtures\Main
 */
class ComponentFixtures extends AbstractFixture implements FixtureInterface
{
    const TEST_COMP_NAME    = 'TestComponent';
    const TEST_COMP_DESC    = 'Test component description';
    const TEST_COMP_ENABLED = true;
    const TEST_COMP_DEFAULT = true;

    const TEST_COMP_TEMP_NAME    = 'TestTempComponent';
    const TEST_COMP_TEMP_DESC    = 'Test temp component description';
    const TEST_COMP_TEMP_ENABLED = true;
    const TEST_COMP_TEMP_DEFAULT = true;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $component = $manager->getRepository('AraneumMainBundle:Component')->findOneByName(self::TEST_COMP_NAME);

        if (empty($component)) {
            $component = new Component();
            $component->setName(self::TEST_COMP_NAME);
            $component->setDescription(self::TEST_COMP_DESC);
            $component->setEnabled(self::TEST_COMP_ENABLED);
            $component->setDefault(self::TEST_COMP_DEFAULT);
            $manager->persist($component);
            $manager->flush();
        }

        $this->addReference('testComponent', $component);

        $tempComponent = $manager->getRepository('AraneumMainBundle:Component')->findOneByName(
            self::TEST_COMP_TEMP_NAME
        );

        if (empty($tempComponent)) {
            $tempComponent = new Component();
            $tempComponent->setName(self::TEST_COMP_TEMP_NAME);
            $tempComponent->setDescription(self::TEST_COMP_TEMP_DESC);
            $tempComponent->setOptions(
                [
                    'test_option_key_1' => 'test_option_value_1',
                    'test_option_key_2' => 'test_option_value_2',
                    'test_option_key_3' => 'test_option_value_3',
                ]
            );
            $tempComponent->setEnabled(self::TEST_COMP_TEMP_ENABLED);
            $tempComponent->setDefault(self::TEST_COMP_TEMP_DEFAULT);
            $manager->persist($tempComponent);
            $manager->flush();
        }
    }
}
