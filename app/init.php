<?php

namespace app;

session_start();

/**
 * Set up autoloading of classes.
 */
spl_autoload_register(function ($className) {
    $filename = __DIR__ . '/../' . str_replace("\\", "/", $className) . ".class.php";
    //var_dump($filename);
    if (file_exists($filename)) {
        include($filename);
        if (class_exists($className)) {
            return TRUE;
        }
    }
    return FALSE;
});

/**
 * Set up basic error handling
 */
set_exception_handler(function (\Exception $ex){
    http_response_code(500);
    echo '<h2>Error!</h2>';
    echo '<p>'. $ex->getMessage() .'</p>';
    echo '<pre>'. $ex->getTraceAsString() .'</pre>';
    die;
});

/**
 * Parse config file and add it to registry.
 */
$config = new lib\Config(__DIR__ . '/../config/config.ini');
lib\Registry::set('config',$config);

/**
 * Connect to database and add handler to registry.
 */
$db = new lib\Database();
lib\Registry::set('db',$db);

/**
 * If there is a user id in session, we add a User object to registry.
 */
if (isset($_SESSION['aFile_User'])) {
    $user = new lib\User($_SESSION['aFile_User']);
    if ($user->getId() !== '0') {
        $user->setKey($_SESSION['aFile_User_Key']);
        lib\Registry::set('user', $user);
    }
}
