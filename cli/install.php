<?php
/** @var \League\CLImate\CLImate $climate */

use lib\Config;
use lib\Installation;

Config::load('config/config.ini.template');

if (Installation::isInstalled()) {
    $climate->red('Install config already exists at config/config.ini. Delete it to reinstall.');
    die;
}

$installation = new Installation();

$configValues = [
    'STORAGE' => './storage/',
    'MAXSIZE' => 20971520
];

$climate->br();
$climate->white()->flank('aFile Install');
$climate->br();

$climate->out('Enter database information. (MySQL/MariaDB)');
$climate->out('Required database tables will be created if missing.');

$climate->br()->out('Hostname:');
$input = $climate->input('>');
$configValues['DB_HOST'] = $input->prompt();

$climate->br()->out('Database name:');
$input = $climate->input('>');
$configValues['DB_DATABASE'] = $input->prompt();

$climate->br()->out('Username:');
$input = $climate->input('>');
$configValues['DB_USER'] = $input->prompt();

$climate->br()->out('Password:');
$input = $climate->password('>');
$configValues['DB_PASSWORD'] = $input->prompt();

$databaseResult = $installation->tryAndConnectToDatabase($configValues['DB_HOST'], $configValues['DB_DATABASE'], $configValues['DB_USER'], $configValues['DB_PASSWORD']);

if (!$databaseResult) {
    $climate->red('ERROR! Failed to establish connection to database!');
    die;
}

$climate->br()->out('Specify where the physical files should be stored (include trailing slash) ['. $configValues['STORAGE'] .']');
$input = $climate->input('>');
$input->defaultTo($configValues['STORAGE']);
$configValues['STORAGE'] = $input->prompt();

if ($configValues['STORAGE'][strlen($configValues['STORAGE'])-1] !== '/') {
    $configValues['STORAGE'] .= '/';
}

$climate->br()->out('Enter the maximum size of file uploads ['. $configValues['MAXSIZE'] .']');
$input = $climate->input('>');
$input->defaultTo($configValues['MAXSIZE']);
$configValues['MAXSIZE'] = $input->prompt();

$climate->br()->out('If an error occurs, should detailed exception information be displayed? [y/N]');
$input = $climate->input('>');
$input->accept(['y', 'n']);
$input->defaultTo('n');
$value = $input->prompt();
$configValues['EXCEPTIONS'] = strtolower($value) === 'y' ? true : false;

$climate->br()->lightCyan()->out('aFile supports a "remember me" functionality, but by using it the user\'s password will be saved encrypted, rather than hashed, in the database, which presents a security risk.');
$climate->br()->out('Enable Remember me? [y/N]');
$input = $climate->input('>');
$input->accept(['y', 'n']);
$input->defaultTo('n');
$value = $input->prompt();
$configValues['REMEMBER_ME'] = strtolower($value) === 'y' ? true : false;

try {
    $climate->br()->inline('Creating database tables...');
    $installation->createTables();
    $climate->green()->inline(' Done!');
} catch (Exception $e) {
    $climate->red()->inline('FAILED!');
    $climate->br()->out('Error message:');
    $climate->out($e->getMessage());
    die;
}

$climate->br()->inline('Writing config...');
$configWritten = $installation->writeConfig($configValues);

if ($configWritten !== false) {
    $climate->green()->inline(' Done!');
}
else {
    $climate->red()->inline('FAILED!');
    $climate->red()->out('Could not write config file.');
    die;
}

$climate->br()->br()->green()->out('aFile was installed successfully.');
