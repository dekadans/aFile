<?php
/**
 * CLI-application for installing and configuring
 */

use League\CLImate\CLImate;
use lib\Config;

if (!file_exists('vendor/autoload.php')) {
    die('ERROR! Run composer install first.'.PHP_EOL);
}

require_once 'vendor/autoload.php';

if (\lib\Installation::isInstalled()) {
    Config::load('config/config.ini');
}

$climate = new CLImate();

$climate->description('Script for installing and configuring aFile.');
try {
    $climate->arguments->add([
        'install' => [
            'longPrefix' => 'install',
            'description' => 'Install aFile',
            'noValue' => true
        ],
        'newUsername' => [
            'longPrefix' => 'add-user',
            'description' => 'Adds a user.'
        ]
    ]);

    $climate->arguments->parse();
} catch (Exception $e) {
    die('CLImate arguments error!');
}

if ($climate->arguments->defined('install')) {
	require('cli/install.php');
}
else if ($climate->arguments->defined('newUsername')) {
	require('cli/addUser.php');
}
else {
	$climate->usage();
}

die;
