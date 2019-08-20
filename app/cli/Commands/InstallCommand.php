<?php

namespace cli\Commands;

use lib\Database;
use lib\DataTypes\DatabaseConfiguration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InstallCommand extends Command
{
    /** @var Database|null  */
    private $database = null;

    private $configReplacements = [];

    private $sqlFilePath = __DIR__ . '/../../../config/sql.php';
    private $configInstallPath = __DIR__ . '/../../../config/config.ini';
    private $configTemplatePath = __DIR__ . '/../../../config/config.ini.template';

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('install');
        $this->setDescription('Run installation of aFile');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Installing aFile');

        if ($this->checkInstalled()) {
            $io->error('aFile is already installed.');
            return;
        }

        $this->promptDatabaseInfo($io);

        $this->promptFileStorage($io);

        $this->promptLoginMode($io);

        $this->createTables();

        $this->writeConfig();

        $io->success('aFile has been installed');
    }

    private function checkInstalled()
    {
        return file_exists($this->configInstallPath);
    }

    private function promptDatabaseInfo(SymfonyStyle $io)
    {
        $io->section('Database');
        $io->text([
            'Enter database information. (MySQL/MariaDB)',
            'Required database tables will be created if missing.'
        ]);

        $hostname = $io->ask('Hostname');
        $database = $io->ask('Database name');
        $username = $io->ask('Username');
        $password = $io->askHidden('Password');

        $databaseConfiguration = new DatabaseConfiguration($hostname, $database, $username, $password);

        try {
            $this->database = new Database($databaseConfiguration);
        } catch (\PDOException $e) {
            $io->error('Failed to establish connection to database!');
            die;
        }

        $this->configReplacements = [
            'DB_HOST' => $hostname,
            'DB_DATABASE' => $database,
            'DB_USER' => $username,
            'DB_PASSWORD' => $password
        ];
    }

    private function promptFileStorage(SymfonyStyle $io)
    {
        $io->section('Files');
        $this->configReplacements['STORAGE'] = $io->ask('Specify where the physical files should be stored (include trailing slash)', './storage/');

        $this->configReplacements['MAXSIZE'] = $io->ask('Enter the maximum size of file uploads in bytes', '20971520');
    }

    private function promptLoginMode(SymfonyStyle $io)
    {
        $io->section('Login');

        $io->text([
            'By default, aFile will keep a user signed in using long lasting cookies unless they explicitly sign out.',
            'If disabled, the user is signed out upon closing the web browser.
        ']);

        $choice = $io->confirm('Keep users signed in?', true);

        $this->configReplacements['REMEMBER_ME'] = $choice ? '1' : '0';
    }

    private function createTables()
    {
        $sqlQueries = require $this->sqlFilePath;

        foreach ($sqlQueries as $query) {
            $this->database->getPDO()->exec($query);
        }
    }

    private function writeConfig()
    {
        $templateConfig = file_get_contents($this->configTemplatePath);
        $config = strtr($templateConfig, $this->configReplacements);
        return file_put_contents($this->configInstallPath, $config);
    }
}