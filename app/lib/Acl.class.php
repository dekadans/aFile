<?php

namespace app\lib;

class Acl {

    /**
     * Checks if a controller is accessible at the current request.
     * Right now it just checks signed in status, but may be extended in the future.
     * @param  AbstractController $controller
     * @return boolean
     */
    public static function checkAccess(\app\controllers\AbstractController $controller) {
        switch ($controller->getAccessLevel()) {
            case \app\controllers\AbstractController::ACCESS_OPEN:
                return true;
            case \app\controllers\AbstractController::ACCESS_LOGIN:
                if (Registry::get('user')) {
                    return true;
                }
                else {
                    return false;
                }
            case \app\controllers\AbstractController::ACCESS_ADMIN:
                if (Registry::get('user') && Registry::get('user')->getType() == 'ADMIN') {
                    return true;
                }
                else {
                    return false;
                }
            default:
                return false;
        }
    }
}
