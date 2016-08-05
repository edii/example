<?php
namespace Araneum\Base\Command;

use MikSoftware\DaemonBundle\Commnad\DaemonizedCommand;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * Demonized Symfony Base command using in project
 */
abstract class AbstractBaseDaemon extends DaemonizedCommand
{
    /**
     * @var array
     */
    const BASE_METHODS = ['status'];

    /**
     * @var integer
     */
    const STATUS_UP   = 0;

    /**
     * @var integer
     */
    const STATUS_DOWN = 1;

    /**
     * @var array
     */
    const DAEMON_STATUS = [
        self::STATUS_UP   => 'Daemon is setting up',
        self::STATUS_DOWN => 'Daemon doesn\'t work',
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct();
        $this->addMethods(self::BASE_METHODS);
    }

    /**
     * {@inheritDoc}
     */
    protected function status()
    {
        $status = self::STATUS_DOWN;
        if ($this->getDaemon()->isRunning()) {
            $status = self::STATUS_UP;
            $this->getOutput()->writeln(self::DAEMON_STATUS[$status]);

            return true;
        }
        $this->getOutput()->writeln(self::DAEMON_STATUS[$status]);

        return false;
    }

    /**
     * Making time interval using in daemon, or throwing Exception
     * @return mixed
     */
    protected function manageTimeIterate()
    {
        $intervals = $this->getContainer()
            ->getParameter('daemons_iterate');
        $timeInterval = $intervals[preg_replace('/:/', '_', $this->getName())];
        if (isset($timeInterval) && !empty($timeInterval)) {
            $time = strtotime($timeInterval) - time();
            if (empty($time) || $time < 0) {
                throw new InvalidParameterException('Interval daemon incorrect format (use: year, month, week, day, hours, minutes, seconds).');
            }
            $this->getDaemon()
                ->iterate($time);
        }
    }

    /**
     * Execute command
     *
     * @inheritdoc
     */
    protected function daemonLogic()
    {
        try {
            $this->logic();
        } catch (\Exception $e) {
            $this->createExceptionLog($e->getMessage());
            sleep($this->reloadProcessWhileExceptionTimeout());
        }
    }

    /**
     * Creates an interval, using
     *
     * @inheritdoc
     */
    protected function reloadProcessWhileExceptionTimeout()
    {
        $intervalString = $this->getContainer()->getParameter('process_exception_timeout_reload');
        if (empty($intervalString)) {
            throw new InvalidParameterException('Please configure \'process_exception_timeout_reload\' parameter');
        }

        $processExceptionInterval = strtotime($intervalString) - time();
        if (empty($processExceptionInterval) || $processExceptionInterval < 0) {
            throw new InvalidParameterException('Interval process exception interval incorrect format (use: year, month, week, day, hours, minutes, seconds).');
        }

        return $processExceptionInterval;
    }

    /**
     * Creates an exception log, while processing an exception
     *
     * @inheritdoc
     */
    protected function createExceptionLog($message)
    {
        $this->getDaemon()->getDaemon()->err($message);
    }

    /**
     * Execute command
     *
     * @inheritdoc
     */
    abstract protected function logic();
}
