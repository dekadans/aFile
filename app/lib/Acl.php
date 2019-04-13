<?php

namespace lib;

use \controllers\AbstractController;
use lib\Repositories\EncryptionKeyRepository;
use lib\Repositories\FileRepository;

class Acl {

    /**
     * @param  AbstractController $controller
     * @return boolean
     */
    public static function checkControllerAccess(AbstractController $controller) : bool
    {
        switch ($controller->getAccessLevel()) {
            case AbstractController::ACCESS_OPEN:
                return true;
            case AbstractController::ACCESS_LOGIN:
                if (Authentication::isSignedIn()) {
                    return true;
                }
                else {
                    return false;
                }
            case AbstractController::ACCESS_ADMIN:
                if (Authentication::isSignedIn() && Authentication::getUser()->getType() == 'ADMIN') {
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
     * @param File $file
     * @param string $linkToken
     * @return boolean
     */
    public static function checkDownloadAccess(File $file, string $linkToken = '') : bool
    {
        if (Authentication::isSignedIn() && Authentication::getUser()->getId() == $file->getUser()->getId()) {
            return true;
        }
        else {
            $fileRepository = new FileRepository();
            $encryptionKeyRepository = new EncryptionKeyRepository($fileRepository);
            $fileToken = $encryptionKeyRepository->findAccessTokenForFile($file);

            if ($fileToken->exists() && in_array($fileToken->getActiveState(), [FileToken::STATE_OPEN, FileToken::STATE_BOTH]) && $fileToken->getOpenToken() === $linkToken) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param AbstractFile $file
     * @return bool
     */
    public static function checkFileAccess(AbstractFile $file) : bool
    {
        /** @var User $user */
        $user = Authentication::getUser();

        if ($user && $user->getId() === $file->getUser()->getId()) {
            return true;
        }
        else {
            return false;
        }
    }
}
