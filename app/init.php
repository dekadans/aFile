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
    $content = '<h2>Error!</h2>';
    if (\lib\Config::getInstance()->show_detailed_exceptions) {
        $content .= '<p>'. $ex->getMessage() .'</p>';
        $content .= '<pre>';
        $content .= $ex->getTraceAsString() . PHP_EOL . PHP_EOL;
        $content .= print_r($ex, true);
        $content .= '</pre>';
    }
    else {
        $content .= '<p>'. \lib\Translation::getInstance()->translate('EXCEPTION_MESSAGE') .'</p>';
    }

    $response = new \lib\HTTP\Response($content, 500);
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
$authentication = new \lib\Authentication($userRepository, \lib\Config::getInstance()->login->remember_me_activated);
$authentication->loadUserFromSession();