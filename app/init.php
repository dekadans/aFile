<?php

session_start();

/**
 * Set up autoloading of classes.
 */
spl_autoload_register(function ($className) {
    $filename = __DIR__ . '/' . str_replace("\\", "/", $className);

    if (file_exists($filename . ".class.php")) {
        include($filename . ".class.php");
    }
    else if (file_exists($filename . ".php")) {
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
 * Parse config file and add it to registry.
 */
$config = new lib\Config(__DIR__ . '/../config/config.ini');
lib\Singletons::set('config',$config);

/**
 * Connect to database and add handler to registry.
 */
$db = new \lib\Database();
\lib\Singletons::$db = $db;

/**
 * Loads language data
 */
$translation = new lib\Translation($config->language);
lib\Singletons::$language = $translation;

\lib\Singletons::$sort = \lib\Sort::loadFromSession();

$userRepository = new \lib\Repositories\UserRepository($db);
$authentication = new \lib\Authentication($userRepository, $config->login->remember_me_activated);
\lib\Singletons::$auth = $authentication;
