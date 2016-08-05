<?php
namespace Araneum\Base\ComposerScripts;

/**
 * Class CheckPaping
 *
 * @package Araneum\Base\ComposerScripts
 */
class CheckPaping
{

    /**
     * @throws \Exception|void
     */
    public static function check()
    {
        exec('paping -p 80 127.0.0.1 -c 1 -t 1', $output, $exitCode);
        if (empty($output) || $exitCode !== 0) {
            throw new \Exception('Need to install paping');
        }
    }
}
