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
            )
            ->addOption(
                'release',
                null,
                InputOption::VALUE_REQUIRED,
                'Allows specifying the release version of Flare to install.',
                ''
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

        $this->installFlare($this->findComposer(), $input, $output);

        $this->updateAppConfig();
    }

    /**
     * Installs Laravel
     * 
     * @return void
     */
    private function installLaravel($composer, OutputInterface $output)
    {
        $process = new Process($composer . ' create-project laravel/laravel=~5.1 . --prefer-dist', null, null, null, null);
        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });
    }

    /**
     * Install Flare
     * 
     * @return void
     */
    private function installFlare($composer, InputInterface $input, OutputInterface $output)
    {
        $version = $input->getOption('release') ? '=' . $input->getOption('release') : '';
        $process = new Process($composer . ' require laravelflare/flare'. $version, null, null, null, null);
        $process->run(function ($type, $line) use ($output) {
            $output->write($line);
        });
    }

    /**
     * Adds the FlareServiceProvider to the config/app.php file 
     * if it hasn't already been addedd.
     * 
     * @return void
     */
    private function updateAppConfig()
    {
        if ($this->fileContains(getcwd().'/config/app.php', 'LaravelFlare\Flare\FlareServiceProvider')) {
            return;
        }

        $this->fileSearchReplace(
            getcwd().'/config/app.php',
            'App\Providers\EventServiceProvider::class,',
            "App\Providers\EventServiceProvider::class,\r\n        LaravelFlare\Flare\FlareServiceProvider::class,");
    }

    /**
     * Determines if a File contains a given string.
     * 
     * @param  string $file   
     * @param  string $search 
     * 
     * @return int
     */
    private function fileContains($file, $search)
    {
        return strpos(file_get_contents($file), $search);
    }
   
    /**
     * Performs a search and replace on a given file.
     * 
     * @param  string $file   
     * @param  string $search 
     * @param  string $replace
     * 
     * @return void
     */
    private function fileSearchReplace($file, $search, $replace)
    {
        file_put_contents($file, str_replace($search, $replace, file_get_contents($file)));
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