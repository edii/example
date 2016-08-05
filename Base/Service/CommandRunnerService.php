<?php

namespace Araneum\Base\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;

/**
 * Class CommandRunnerService
 *
 * @package Araneum\Base\Service
 */
class CommandRunnerService
{
    /** @var KernelInterface */
    private $kernel;

    /**
     * CommandRunnerService constructor.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Run Symfony command in new process.
     *
     * @param string $input
     */
    public function runSymfonyCommandInNewProcess($input)
    {
        $rootDir = $this->kernel->getRootDir();
        $environment = $this->kernel->getEnvironment();
        $input = $rootDir.'/console '.$input.' -e '.$environment;
        $deploymentCommand = new Process($input, null, null, null, null);
        $deploymentCommand->mustRun();
    }

    /**
     * Run Symfony deployment command as separate process.
     *
     * @param  string $command
     * @param  string $path
     * @return string
     */
    public function runDeployCommandsInSeparateProcess($command, $path = '')
    {
        $rootDir = $this->kernel->getRootDir();
        $deploymentCommand = new Process($command, $rootDir.$path, null, null, null);
        $deploymentCommand->start();
        $message = '';
        $deploymentCommand->wait(
            function ($type, $buffer) use (&$message) {
                if (Process::ERR === $type) {
                    $message = 'ERR > '.$buffer;
                } else {
                    $message = 'OUT > '.$buffer;
                }
            }
        );

        return $message;
    }
}
