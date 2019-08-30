<?php
/**
 * @var \Psr\Container\ContainerInterface $container
 */

require_once '../vendor/autoload.php';

session_start();

$container = require '../app/container.php';
require_once '../app/webinit.php';

$controller = new \controllers\Download($container);
$response = $controller->index();
printResponse($response);
die;