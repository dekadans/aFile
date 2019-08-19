<?php

use cli\Commands\AddUserCommand;
use cli\Commands\InstallCommand;
use cli\Commands\KeyCommand;
use cli\Commands\PasswordCommand;
use lib\Repositories\UserRepository;
use Symfony\Component\Console\Application;

require __DIR__ . '/../../vendor/autoload.php';

/** @var \Psr\Container\ContainerInterface $container */
$container = require __DIR__ . '/../container.php';

$application = new Application('aFile CLI Tool');

$application->add(new PasswordCommand($container->get(UserRepository::class)));
$application->add(new KeyCommand($container->get(UserRepository::class)));
$application->add(new AddUserCommand($container->get(UserRepository::class)));
$application->add(new InstallCommand());

$application->run();