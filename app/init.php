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
 * Include the Defuse cryptography library
 */
require_once(__DIR__ . '/defuse-crypto.phar');


/**
 * Parse config file and add it to registry.
 */
$config = new lib\Config(__DIR__ . '/../config/config.ini');
lib\Registry::set('config',$config);

/**
 * Connect to database and add handler to registry.
 */
$db = new \lib\Database();
lib\Registry::set('db',$db);

/**
 * Loads language data
 */
$translation = new lib\Translation($config->language);
lib\Registry::$language = $translation;

/**
 * If there is a user id in session, we add a User object to registry.
 */
if (isset($_SESSION['aFile_User'])) {
    $user = new \lib\User($_SESSION['aFile_User']);
    if ($user->getId() !== '0') {
        $user->setKey($_SESSION['aFile_User_Key']);
        lib\Registry::set('user', $user);
    }
}
