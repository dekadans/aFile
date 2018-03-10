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
     * @param string $token
     * @return boolean
     */
    public static function checkDownloadAccess(Download $download, $token) {
        if (Registry::get('user') && Registry::get('user')->getId() == $download->getFile()->getUser()->getId()) {
            return true;
        }
        else {
            $sharingInfo = $download->getFile()->getSharingInfo();

            if (is_array($sharingInfo) && in_array($sharingInfo['active'], [Sharing::STATE_OPEN, Sharing::STATE_BOTH]) && $sharingInfo['open_token'] === $token) {
                return true;
            }
        }

        return false;
    }
}
