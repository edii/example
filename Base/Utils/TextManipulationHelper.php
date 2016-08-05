<?php
namespace Araneum\Base\Utils;

/**
 * Class TextManipulationHelper
 *
 * @package Araneum\Base\Utils
 */
class TextManipulationHelper
{

    /**
     * Trancate the string to a specified limit of symbols and breakpoin
     *
     * @param string $string
     * @param int    $limit
     * @param string $break
     * @param string $pad
     * @return string
     */
    public static function truncateString($string, $limit, $break = ' ', $pad = '...')
    {
        if (strlen($string) <= $limit) {
            return $string;
        }

        if (false !== ($breakpoint = strpos($string, $break, $limit))) {
            if ($breakpoint < strlen($string) - 1) {
                $string = substr($string, 0, $breakpoint).$pad;
            }
        }

        return $string;
    }
}
