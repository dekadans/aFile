<?php
use League\CLImate\CLImate;
use lib\Config;

if (!file_exists('vendor/autoload.php')) {
    die('ERROR! Run composer install first.'.PHP_EOL);
}

require_once 'app/autoload.php';
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
        'addUser' => [
            'longPrefix' => 'add-user',
            'description' => 'Adds a user. Specify the new username with -u',
            'noValue' => true
        ],
        'username' => [
            'prefix' => 'u',
            'description' => 'A username'
        ]

    ]);

    $climate->arguments->parse();
} catch (Exception $e) {
    die('CLImate arguments error!');
}

if ($climate->arguments->get('install')) {
	require('cli/install.php');
}
else if ($climate->arguments->get('addUser')) {
	require('cli/addUser.php');
}
else {
	$climate->usage();
}

die;
