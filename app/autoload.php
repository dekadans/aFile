<?php

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
