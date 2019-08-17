<?php

session_start();

$port = (!in_array($_SERVER['SERVER_PORT'], [80, 443]) ? ':' . $_SERVER['SERVER_PORT'] : '');
define('AFILE_LOCATION', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . $port . preg_replace('/[a-z]*\.php[a-z0-9\/]*/', '', $_SERVER['PHP_SELF']));

/**
 * Autoloading aFile classes
 */
require __DIR__ . '/autoload.php';

/**
 * Set up basic error handling
 */
set_exception_handler(function (\Throwable $ex){
    $response = new \lib\HTTP\HTMLResponse('partials/exceptionError', ['exception' => $ex], 500);
    printResponse($response->psr7());
    die;
});

/**
 * Include packages from Composer
 */
require_once __DIR__ . '/../vendor/autoload.php';

if (!file_exists(__DIR__ . '/../config/config.ini')) {
    $response = new \lib\HTTP\Response('No config found. Please run installation script.', 500);
    printResponse($response->psr7());
    die;
}

/**
 * Parse config file.
 */
\lib\Config::load(__DIR__ . '/../config/config.ini');

/**
 * Loads language data
 */
\lib\Translation::loadLanguage(\lib\Config::getInstance()->language);

\lib\Sort::loadFromSession();

$userRepository = new \lib\Repositories\UserRepository(\lib\Database::getInstance());
$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

$authenticationService = new \lib\Services\AuthenticationService($userRepository);
$authenticationService->load($request);


function printResponse(\Psr\Http\Message\ResponseInterface $response)
{
    http_response_code($response->getStatusCode());

    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header(sprintf('%s: %s', $name, $value), false);
        }
    }

    $stream = $response->getBody();

    while (!$stream->eof()) {
        echo $stream->read(100000);
    }

    $stream->close();
}
