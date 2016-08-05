<?php
namespace Araneum\Base\Traits;

/**
 * Class MockHelperTrait
 *
 * @package Araneum\Base\Traits
 */
trait MockHelperTrait
{
    /**
     * Fast way to generate mock for class
     *
     * @param string $class
     * @param bool   $disableOriginalConstructor Disable the call to the original class' constructor
     * @param array  $mockMethods                Only this methods will be mocked
     * @return mixed
     */
    protected function buildMock($class, $disableOriginalConstructor = true, array $mockMethods = [])
    {
        $mock = $this->getMockBuilder($class);
        if (!empty($mockMethods)) {
            $mock->setMethods($mockMethods);
        }

        if (!empty($disableOriginalConstructor)) {
            $mock->disableOriginalConstructor();
        }

        return $mock->getMock();
    }
}
