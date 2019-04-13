<?php

namespace lib\Repositories;

use Defuse\Crypto\Key;
use lib\Authentication;
use lib\Database;
use lib\DataTypes\User;
use lib\DataTypes\File;
use lib\DataTypes\FileToken;

class EncryptionKeyRepository
{
    const ENCRYPTION_PERSONAL = 'PERSONAL';
    const ENCRYPTION_TOKEN = 'TOKEN';

    /** @var \PDO */
    private $pdo;
    /** @var FileRepository */
    private $fileRepository;
    /** @var User */
    private $user;

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
        $this->pdo = Database::getInstance()->getPDO();
        $this->user = Authentication::getUser();
    }

    public function getEncryptionKeyForFile(File $file) : string
    {
        if ($file->getEncryption() === self::ENCRYPTION_PERSONAL && isset($this->user) && $file->getUser()->getId() === $this->user->getId()) {
            $key = $this->user->getKey();
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

    public function findAccessTokenForFile(File $file)
    {
        $shareQuery = $this->pdo->prepare('SELECT * FROM share WHERE file_id = ?');
        $shareQuery->execute([$file->getId()]);
        $shareData = $shareQuery->fetch();

        if ($shareData) {
            return FileToken::createFromArray($shareData);
        }
        else {
            return null;
        }
    }

    public function createAccessTokenForFile(File $file)
    {
        $token = $this->findAccessTokenForFile($file);

        if ($token) {
            return $token;
        } else {
            $encryptionKey = Key::createNewRandomKey();
            $encryptionKeyAscii = $encryptionKey->saveToAsciiSafeString();
            $newTokenString = $this->generateToken();

            $SQL = "INSERT INTO share (file_id, open_token, active, encryption_key)
                    VALUES (:fileId, :token, 'OPEN', :encryptionKey)";

            $createStatement = $this->pdo->prepare($SQL);
            $createStatement->bindValue(':fileId', $file->getId());
            $createStatement->bindValue(':token', $newTokenString);
            $createStatement->bindValue(':encryptionKey', $encryptionKeyAscii);

            if ($createStatement->execute()) {
                $result = $this->fileRepository->changeEncryptionKeyForFile($file, $encryptionKeyAscii, self::ENCRYPTION_TOKEN);

                if ($result) {
                    return $this->findAccessTokenForFile($file);
                }
            }

            return false;
        }
    }

    public function removeAccessTokenForFile(File $file)
    {
        $token = $this->findAccessTokenForFile($file);

        if (!$token) {
            return true;
        } else {
            $newEncryptionKey = $this->user->getKey();
            $result = $this->fileRepository->changeEncryptionKeyForFile($file, $newEncryptionKey, self::ENCRYPTION_PERSONAL);

            if ($result) {
                $SQL = "DELETE from share WHERE id = :id";
                $deleteStatement = $this->pdo->prepare($SQL);
                $deleteStatement->bindValue(':id', $token->getId());
                return $deleteStatement->execute();
            } else {
                return false;
            }
        }
    }

    private function generateToken()
    {
        return sha1(random_bytes(32));
    }
}

class CouldNotLocateEncryptionKeyException extends \Exception {}