<?php
require_once '../app/init.php';

// Dev user info: username: tomas password: aabbcc

if (isset($_GET['do']) && !empty($_GET['do'])) {
    $do = '\\controllers\\' . $_GET['do'];

    if (class_exists($do)) {
        /** @var \controllers\AbstractController $controller */
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
                \controllers\AbstractController::outputJSON([
                    'error' => lib\Translation::getInstance()->translate('NOT_FOUND')
                ]);
            }
        }
        else {
            \controllers\AbstractController::outputJSON([
                'error' => lib\Translation::getInstance()->translate('ACCESS_DENIED')
            ]);
        }
    }
    else {
        \controllers\AbstractController::outputJSON([
            'error' => lib\Translation::getInstance()->translate('NOT_FOUND')
        ]);
    }
}
