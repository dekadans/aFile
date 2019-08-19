<?php

use Symfony\Component\Console\Application;

require __DIR__ . '/../../vendor/autoload.php';

/** @var \Psr\Container\ContainerInterface $container */
$container = require __DIR__ . '/../container.php';

$application = new Application('aFile CLI Tool');

$application->add(new \cli\Commands\PasswordCommand($container->get(\lib\Repositories\UserRepository::class)));
$application->add(new \cli\Commands\KeyCommand($container->get(\lib\Repositories\UserRepository::class)));

$application->run();