<?php
use lib\Config;

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
$climate->white()->flank('aFile Install');
$climate->br();

$climate->green('Step 1: Configure environment');

$climate->br()->inline('Using default database driver: ');
$climate->green()->out(Config::getInstance()->database->driver);
$climate->out('Required database tables will be created if missing.');
$climate->out('Enter database information.');
$input = $climate->input('Hostname:');
$configValues['DB_HOST'] = $input->prompt();
$input = $climate->input('Database name:');
$configValues['DB_DATABASE'] = $input->prompt();
$input = $climate->input('Username:');
$configValues['DB_USER'] = $input->prompt();
$input = $climate->password('Password:');
$configValues['DB_PASSWORD'] = $input->prompt();

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

