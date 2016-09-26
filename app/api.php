<?php
namespace app;

require_once 'init.php';

// Dev user info: username: tomas password: aabbcc

if (isset($_GET['do']) && !empty($_GET['do'])) {
    $do = '\\app\\controllers\\' . $_GET['do'];

    if (class_exists($do)) {
        $controller = new $do();

        if (lib\Acl::checkAccess($controller)) {
            $controller->index();
        }
        else {
            echo controllers\AbstractController::outputJSON([
                'error' => 'ACCESS_DENIED'
            ]);
        }
    }
    else {
        echo controllers\AbstractController::outputJSON([
            'error' => 'NOT_FOUND'
        ]);
    }
}
