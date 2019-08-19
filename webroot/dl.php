<?php
/**
 * @var \Psr\Container\ContainerInterface $container
 */

require_once '../app/webinit.php';
$container = require '../app/container.php';

$controller = new \controllers\Download($container);
$response = $controller->index();
printResponse($response);
die;