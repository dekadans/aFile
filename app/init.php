<?php

session_start();

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
    http_response_code(500);
    echo '<h2>Error!</h2>';
    echo '<p>'. $ex->getMessage() .'</p>';
    echo '<pre>'. $ex->getTraceAsString() .'</pre>';
    var_dump($ex);
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
$authentication = new \lib\Authentication($userRepository, \lib\Config::getInstance()->login->remember_me_activated);
$authentication->loadUserFromSession();