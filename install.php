<?php
use League\CLImate\CLImate;
use lib\Config;

if (!file_exists('vendor/autoload.php')) {
    die('ERROR! Run composer install first.'.PHP_EOL);
}

require_once 'vendor/autoload.php';
require_once 'app/lib/Config.php';

Config::load('config/config.ini.example');
$climate = new CLImate();

if (file_exists('config/config.ini')) {
    $climate->red('Install config already exists at config/config.ini. Delete it to reinstall.');
    die;
}

$configValues = [
    'STORAGE' => './storage/',
    'MAXSIZE' => 20971520,
    'EXCEPTIONS' => false
];

$climate->br();
$climate->flank('aFile Install');
$climate->br();

$climate->out('Step 1: Configure environment');

$database = [];
$climate->br()->inline('Using default database driver: ');
$climate->green()->out(Config::getInstance()->database->driver);
$climate->out('Required database tables will be created if missing.');
$input = $climate->input('Enter hostname:');
$database['HOST'] = $input->prompt();
$input = $climate->input('Enter database name:');
$database['DATABASE'] = $input->prompt();
$input = $climate->input('Enter username:');
$database['USER'] = $input->prompt();
$input = $climate->password('Enter password:');
$database['PASSWORD'] = $input->prompt();
$configValues['DB'] = $database;

// TRY TO CONNECT TO DB, ABORT IF FAILURE

$input = $climate->input('Specify where the physical files should be stored ['. $configValues['STORAGE'] .']');
$input->defaultTo($configValues['STORAGE']);
$climate->br();
$configValues['STORAGE'] = $input->prompt();

$input = $climate->input('Enter the maximum size of file uploads ['. $configValues['MAXSIZE'] .']');
$input->defaultTo($configValues['MAXSIZE']);
$climate->br();
$configValues['MAXSIZE'] = $input->prompt();

$input = $climate->input('If an error occurs, should detailed exception information be displayed? [y/N]');
$input->accept(['y', 'n']);
$input->defaultTo('n');
$climate->br();
$value = $input->prompt();
$configValues['EXCEPTIONS'] = strtolower($value) === 'y' ? true : false;


$climate->dump($configValues);