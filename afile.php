<?php
use League\CLImate\CLImate;
use lib\Config;

if (!file_exists('vendor/autoload.php')) {
    die('ERROR! Run composer install first.'.PHP_EOL);
}

require_once 'app/autoload.php';
require_once 'vendor/autoload.php';

Config::load('config/config.ini.example');
$climate = new CLImate();

$climate->description('Script for installing and configuring aFile.');
$climate->arguments->add([
	'install' => [
		'longPrefix' => 'install',
		'description' => 'Install aFile',
		'noValue' => true
	]

]);

$climate->arguments->parse();

if ($climate->arguments->get('install')) {
	require('cli/install.php');
}
else {
	$climate->usage();
}

die;
