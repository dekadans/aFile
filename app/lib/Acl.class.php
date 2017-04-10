<?php

namespace lib;

use \controllers\AbstractController;

class Acl {

    /**
     * Checks if a controller is accessible at the current request.
     * Right now it just checks signed in status, but may be extended in the future.
     * @param  AbstractController $controller
     * @return boolean
     */
    public static function checkAccess(AbstractController $controller) {
        switch ($controller->getAccessLevel()) {
            case AbstractController::ACCESS_OPEN:
                return true;
            case AbstractController::ACCESS_LOGIN:
                if (Registry::get('user')) {
                    return true;
                }
                else {
                    return false;
                }
            case AbstractController::ACCESS_ADMIN:
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

    /**
     * Checks access to a requested download
     * @param  Download $download
     * @return boolean
     */
    public static function checkDownloadAccess(Download $download) {
        if (Registry::get('user') && Registry::get('user')->getId() == $download->getFile()->getUser()->getId()) {
            return true;
        }

        // Sharing logic should be here

        return false;
    }
}
