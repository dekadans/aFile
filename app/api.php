<?php
require_once 'init.php';

// Dev user info: username: tomas password: aabbcc

if (isset($_GET['do']) && !empty($_GET['do'])) {
    $do = '\\controllers\\' . $_GET['do'];

    if (class_exists($do)) {
        $controller = new $do();

        if (\lib\Acl::checkAccess($controller)) {
            if (method_exists($controller, 'init')) {
                $controller->init();
            }

            $action = $controller->param('action');

            if ($action) {
                $action = 'action' . ucfirst($action);
            }
            else {
                $action = 'index';
            }

            if (method_exists($controller, $action)) {
                $controller->$action();
            }
            else {
                echo \controllers\AbstractController::outputJSON([
                    'error' => 'NOT_FOUND'
                ]);
            }
        }
        else {
            echo \controllers\AbstractController::outputJSON([
                'error' => 'ACCESS_DENIED'
            ]);
        }
    }
    else {
        echo \controllers\AbstractController::outputJSON([
            'error' => 'NOT_FOUND'
        ]);
    }
}
