<?php

namespace lib\Factories;

use lib\Database;
use lib\Repositories\EncryptionKeyRepository;
use lib\Repositories\FileRepository;
use lib\Repositories\UserRepository;
use lib\Services\AuthenticationService;
use lib\Services\EncryptionService;

class FileRepositoryFactory
{
    public static function create(AuthenticationService $authenticationService)
    {
        $database = Database::getInstance();
        $userRepository = new UserRepository($database);
        $encryptionService = new EncryptionService();
        $encryptionKeyRepository = new EncryptionKeyRepository($encryptionService, $authenticationService->getUser());

        $fileRepository = new FileRepository($database, $userRepository, $encryptionService, $encryptionKeyRepository);

        return $fileRepository;
    }
}