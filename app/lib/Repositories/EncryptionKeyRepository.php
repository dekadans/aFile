<?php

namespace lib\Repositories;

use lib\Authentication;
use lib\Database;
use lib\File;
use lib\FileToken;

class EncryptionKeyRepository
{
    const ENCRYPTION_PERSONAL = 'PERSONAL';
    const ENCRYPTION_TOKEN = 'TOKEN';

    /** @var \PDO */
    private $pdo;
    /** @var FileRepository */
    private $fileRepository;

    public function __construct(FileRepository $fileRepository)
    {
        $this->pdo = Database::getInstance()->getPDO();
        $this->fileRepository = $fileRepository;
    }

    public function getEncryptionKeyForFile(File $file) : string
    {
        $signedInUser = Authentication::getUser();

        if ($file->getEncryption() === self::ENCRYPTION_PERSONAL && $file->getUser()->getId() === $signedInUser->getId()) {
            $key = $signedInUser->getKey();
        } else if ($file->getEncryption() === self::ENCRYPTION_TOKEN) {
            $fileToken = $this->findAccessTokenForFile($file);
            $key = $fileToken->getEncryptionKey();
        }

        if (isset($key)) {
            return $key;
        } else {
            throw new CouldNotLocateEncryptionKeyException('No key found for file with ID ' . $file->getId() . '.');
        }
    }

    public function findAccessTokenForFile(File $file) : FileToken
    {
        $shareQuery = Database::getInstance()->getPDO()->prepare('SELECT * FROM share WHERE file_id = ?');
        $shareQuery->execute([$file->getId()]);
        $shareData = $shareQuery->fetch();

        if ($shareData) {
            return FileToken::createFromArray($shareData, $this->fileRepository);
        }
        else {
            return FileToken::createFromArray(['id' => null, 'file_id' => $file->getId()], $this->fileRepository);
        }
    }
}

class CouldNotLocateEncryptionKeyException extends \Exception {}