<?php

namespace lib;

use \controllers\AbstractController;
use lib\DataTypes\AbstractFile;
use lib\DataTypes\File;
use lib\DataTypes\FileToken;
use lib\Repositories\EncryptionKeyRepository;
use lib\Repositories\FileRepository;
use lib\Services\AuthenticationService;

class Acl {

    const DOWNLOAD_ACCESS_APPROVED = 1;
    const DOWNLOAD_ACCESS_PASSWORD = 2;
    const DOWNLOAD_ACCESS_DENIED = 3;

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
                if (AuthenticationService::isSignedIn()) {
                    return true;
                }
                else {
                    return false;
                }
            case AbstractController::ACCESS_ADMIN:
                if (AuthenticationService::isSignedIn() && AuthenticationService::getUser()->getType() == 'ADMIN') {
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
     * @param string $urlToken
     * @param string $password
     * @return int
     */
    public static function checkDownloadAccess(File $file, string $urlToken = '', string $password = '') : int
    {
        if (AuthenticationService::isSignedIn() && AuthenticationService::getUser()->getId() == $file->getUser()->getId()) {
            return self::DOWNLOAD_ACCESS_APPROVED;
        }
        else {
            $fileRepository = new FileRepository();
            $encryptionKeyRepository = new EncryptionKeyRepository($fileRepository);
            $fileToken = $encryptionKeyRepository->findAccessTokenForFile($file);

            if ($fileToken && $fileToken->getToken() === $urlToken) {
                if ($fileToken->getActiveState() === FileToken::STATE_OPEN) {
                    return self::DOWNLOAD_ACCESS_APPROVED;
                } else if ($fileToken->getActiveState() === FileToken::STATE_RESTRICTED && !empty($fileToken->getPasswordHash())) {
                    if (!empty($password) && password_verify($password, $fileToken->getPasswordHash())) {
                        return self::DOWNLOAD_ACCESS_APPROVED;
                    } else {
                        return self::DOWNLOAD_ACCESS_PASSWORD;
                    }
                }
            }
        }

        return self::DOWNLOAD_ACCESS_DENIED;
    }

    /**
     * @param AbstractFile $file
     * @return bool
     */
    public static function checkFileAccess(AbstractFile $file) : bool
    {
        $user = AuthenticationService::getUser();

        if ($user && $user->getId() === $file->getUser()->getId()) {
            return true;
        }
        else {
            return false;
        }
    }
}
