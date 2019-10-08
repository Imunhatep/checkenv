<?php
declare(strict_types=1);

namespace Checkenv\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;

class ConfigCheckEnvironmentCommand extends Command
{
    protected static $defaultName = 'config:check:environment';

    private $rootPath;

    public function __construct()
    {
        parent::__construct();
        $this->rootPath = getcwd();
    }

    protected function configure()
    {
        $this
            ->setDescription(
                'Check variables in application environment against variables defined in dotEnv'
            )
            ->addOption(
                'dot-env',
                null,
                InputOption::VALUE_OPTIONAL,
                'dotEnv filename in project root dir',
                '.env'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $envFilename = $input->getOption('dot-env');
        if (empty($envFilename)) {
            throw new \InvalidArgumentException('Unexpected dot-env argument value received');
        }

        // is provided dotenv filepath is absolute
        $envFilepath = ($envFilename[0] === '\\')
            ? $envFilename
            : $this->rootPath . DIRECTORY_SEPARATOR . $envFilename;

        if (!is_readable($envFilepath)) {
            throw new \InvalidArgumentException(
                'Cannot read provided dotEnv file at: ' . $envFilepath
            );
        }

        $output->writeln('Loading env file from: ' . $envFilepath);

        $envVars = (new Dotenv)->parse(file_get_contents($envFilepath), $envFilename);
        foreach ($envVars as $key => $value) {
            if (!$this->isSetEnv($key)) {
                throw new \RuntimeException(
                    sprintf(
                        'Variable "%s" defined in "%s" file is not set for current environment',
                        $key,
                        $envFilename
                    )
                );
            }
        }

        $output->writeln('OK');
    }

    private function isSetEnv(string $key): bool
    {
        return getenv($key) !== false || array_key_exists($key, $_ENV);
    }
}