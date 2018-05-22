<?php

session_start();

define('AFILE_LOCATION', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . preg_replace('/[a-z]*\.php[a-z1-9\/]*/', '', $_SERVER['PHP_SELF']));

/**
 * Set up autoloading of classes.
 */
spl_autoload_register(function ($className) {
    $filename = __DIR__ . '/' . str_replace("\\", "/", $className);

    if (file_exists($filename . ".php")) {
        include($filename . ".php");
    }

    if (class_exists($className)) {
        return TRUE;
    }
    return FALSE;
});

/**
 * Set up basic error handling
 */
set_exception_handler(function (\Throwable $ex){
    $response = new \lib\HTTP\HTMLResponse('exceptionError', ['exception' => $ex], 500);
    echo $response->output();
    die;
});

/**
 * Include packages from Composer
 */
require_once __DIR__ . '/../vendor/autoload.php';


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
$authentication = new \lib\Authentication($userRepository, $request, \lib\Config::getInstance()->login->remember_me_activated);
$authentication->loadUserFromSession();