<?php

namespace Araneum\Base\MikSoftware\DaemonBundle\Daemon;

use MikSoftware\DaemonBundle\Daemon\Daemon as BaseDaemon;

/**
 * Class Daemon
 *
 * @package Araneum\Base\MikSoftware\DaemonBundle\Daemon
 */
class Daemon extends BaseDaemon
{
    /**
     * @var array
     */
    protected $daemonRelatedPids;

    /**
     * Stop daemon process.
     *
     * @return void
     * @see stop()
     */
    public function stop()
    {
        $pid = $this->isRunning();
        if ($pid && !$this->isDying) {
            $graph = [
                $pid => $pid,
            ];

            $this->createDaemonChildrensGraph($graph);
            $this->info('Stopping {appName} ['.$pid.']');
            $this->ddie(false, implode(' ', $this->daemonRelatedPids));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isRunning()
    {
        $pid = $this->getDaemonPid();
        if (!$pid) {
            return false;
        }

        exec('ps -p '.$pid, $op);
        if (empty($op[1])) {
            return false;
        }

        return $pid;
    }

    protected function changeIdentity($gid = 0, $uid = 0)
    {
        // Change identity. maybe
        if (posix_geteuid() === 0) {
            return parent::changeIdentity($gid, $uid);
        }

        return true;
    }

    /**
     * Gets daemon pid
     *
     * @return mixed
     */
    protected function getDaemonPid()
    {
        $filepath = $this->getOption('appPidLocation');
        $f = @fopen($filepath, 'r');
        if (!$f) {
            return false;
        }
        $data = fread($f, filesize($filepath));
        fclose($f);

        return $data;
    }

    /**
     * Get childrens of process by PID
     *
     * @param int $pid
     * @return mixed
     */
    private function getProcessChildrenPids($pid)
    {
        if (!empty($pid)) {
            $result = [];
            $array = explode(' ', exec('pgrep -d \' \' -P '.$pid.' 2>/dev/null'));
            foreach ($array as $item) {
                $result[$item] = [];
            }

            return $result;
        }

        return false;
    }

    /**
     * Recursive getting all pids of the process
     *
     * @param array $graph
     * @return mixed
     */
    private function createDaemonChildrensGraph($graph)
    {
        $keys = array_keys($graph);
        for ($i = 0; $i < count($keys); $i++) {
            $key = $keys[$i];

            if (!in_array($key, $this->daemonRelatedPids)) {
                $this->daemonRelatedPids[] = $key;
                $array = $this->getProcessChildrenPids($key);

                if (!empty($array)) {
                    $graph[$key] = $array;
                    $this->createDaemonChildrensGraph($graph[$key]);
                }
            }
        }
    }
}
