<?php

namespace Araneum\Base\MikSoftware\DaemonBundle\Services;

use Araneum\Base\MikSoftware\DaemonBundle\Daemon\Daemon;
use MikSoftware\DaemonBundle\Services\DaemonService as BaseService;

/**
 * Class DaemonService
 *
 * @package Araneum\Base\MikSoftware\DaemonBundle\Service
 */
class DaemonService extends BaseService
{
    /**
     * Initialize the service
     *
     * @param array $options
     * @throws \MikSoftware\DaemonBundle\Daemon\DaemonException
     */
    public function initialize($options)
    {
        parent::initialize($options);
        $this->setDaemon(new Daemon($this->getConfig()));
    }
}
