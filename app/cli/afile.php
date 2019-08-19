<?php

use Symfony\Component\Console\Application;

require __DIR__ . '/../../vendor/autoload.php';

/** @var \Psr\Container\ContainerInterface $container */
$container = require __DIR__ . '/../container.php';

$application = new Application();

$application->add(new \cli\Commands\TestCommand($container->get(\lib\Repositories\FileRepository::class)));

$application->run();