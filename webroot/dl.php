<?php
require_once '../app/init.php';

$controller = new \controllers\Download(\GuzzleHttp\Psr7\ServerRequest::fromGlobals());
$response = $controller->index();
printResponse($response);
die;