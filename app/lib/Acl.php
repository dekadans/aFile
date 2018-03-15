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
            $fileToken = $download->getFile()->getToken();

            if ($fileToken->exists() && in_array($fileToken->getActiveState(), [FileToken::STATE_OPEN, FileToken::STATE_BOTH]) && $fileToken->getOpenToken() === $token) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AbstractFile $file
     * @return bool
     */
    public static function checkFileAccess(AbstractFile $file)
    {
        /** @var User $user */
        $user = Registry::get('user');

        if ($user && $user->getId() === $file->getUser()->getId()) {
            return true;
        }
        else {
            return false;
        }
    }
}
