<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;
use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;

class ServeCommand extends BaseServeCommand
{
    /**
     * Start a new server process.
     *
     * Windows can fail to bind when Laravel strips most env vars in reload mode.
     * Keep the full env when spawning the PHP built-in server.
     *
     * @param  bool  $hasEnvironment
     */
    protected function startProcess($hasEnvironment): Process
    {
        $process = new Process(
            $this->serverCommand(),
            public_path(),
            (new Collection($_ENV))
                ->merge(['PHP_CLI_SERVER_WORKERS' => $this->phpServerWorkers])
                ->all()
        );

        $signals = array_values(array_filter([
            defined('SIGTERM') ? SIGTERM : null,
            defined('SIGINT') ? SIGINT : null,
            defined('SIGHUP') ? SIGHUP : null,
            defined('SIGUSR1') ? SIGUSR1 : null,
            defined('SIGUSR2') ? SIGUSR2 : null,
            defined('SIGQUIT') ? SIGQUIT : null,
        ]));

        $this->trap(fn () => $signals, function ($signal) use ($process) {
            if ($process->isRunning()) {
                $process->stop(10, $signal);
            }

            exit;
        });

        $process->start($this->handleProcessOutput());

        return $process;
    }
}
