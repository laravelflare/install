<?php

namespace LaravelFlare\Install\Console;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();
        $this
            ->setName('install')
            ->setDescription('Install Flare into the current directory.')
             ->addArgument(
                'clean',
                InputArgument::OPTIONAL,
                "Provides a completely clean Laravel install with Flare on top."
            );
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * 
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cleanInstall = $input->getArgument('clean') ? true : false;

        if ($cleanInstall) {
            $this->installLaravel($this->findComposer(), $output);
        }

        $this->installFlare($this->findComposer(), $output);
    }

    /**
     * Installs Laravel
     * 
     * @return void
     */
    private function installLaravel($composer, OutputInterface $output)
    {
        $process = new Process($composer . ' require laravel/laravel=~5.1', null, null, null, null);
        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });
    }

    /**
     * Install Flare
     * 
     * @return void
     */
    private function installFlare($composer, OutputInterface $output)
    {
        $process = new Process($composer . ' require laravelflare/flare', null, null, null, null);
        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });
    }

    /**
     * Get the composer command for the environment.
     *
     * @return string
     */
    private function findComposer()
    {
        if (!file_exists(getcwd() . '/composer.phar')) {
            return 'composer';
        }

        return '"' . PHP_BINARY . '" composer.phar"';
    }
}