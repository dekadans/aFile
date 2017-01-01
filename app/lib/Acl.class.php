<?php

namespace lib;

class Acl {

    /**
     * Checks if a controller is accessible at the current request.
     * Right now it just checks signed in status, but may be extended in the future.
     * @param  AbstractController $controller
     * @return boolean
     */
    public static function checkAccess(\controllers\AbstractController $controller) {
        switch ($controller->getAccessLevel()) {
            case \controllers\AbstractController::ACCESS_OPEN:
                return true;
            case \controllers\AbstractController::ACCESS_LOGIN:
                if (Registry::get('user')) {
                    return true;
                }
                else {
                    return false;
                }
            case \controllers\AbstractController::ACCESS_ADMIN:
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
