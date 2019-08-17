<?php
/**
 * Set in init.php
 * @var \Psr\Http\Message\ServerRequestInterface $request
 * @var \lib\Services\AuthenticationService $authenticationService
 */

require_once '../app/init.php';

$controller = new \controllers\Download($request, $authenticationService);
$response = $controller->index();
printResponse($response);
die;