<?php

namespace lib\Repositories;

use Defuse\Crypto\Key;
use lib\Database;
use lib\DataTypes\User;
use lib\DataTypes\File;
use lib\DataTypes\FileToken;
use lib\Exceptions\CouldNotLocateEncryptionKeyException;
use lib\Services\AuthenticationService;
use lib\Services\EncryptionService;

class EncryptionKeyRepository
{
    const ENCRYPTION_PERSONAL = 'PERSONAL';
    const ENCRYPTION_TOKEN = 'TOKEN';

    /** @var \PDO */
    private $pdo;
    /** @var User */
    private $user;
    /** @var EncryptionService */
    private $encryptionService;

    public function __construct(EncryptionService $encryptionService, Database $database, AuthenticationService $authenticationService = null)
    {
        $this->encryptionService = $encryptionService;
        $this->pdo = $database->getPDO();
        $this->user = $authenticationService->getUser();
    }

    /**
     * @param File $file
     * @return string
     * @throws CouldNotLocateEncryptionKeyException
     */
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

    /**
     * @param File $file
     * @return FileToken|null
     */
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

    /**
     * @param File $file
     * @return bool|FileToken|null
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
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
                $result = $this->changeEncryptionKeyForFile($file, $encryptionKeyAscii, self::ENCRYPTION_TOKEN);

                if ($result) {
                    return $this->findAccessTokenForFile($file);
                }
            }

            return false;
        }
    }

    /**
     * @param File $file
     * @return bool
     */
    public function removeAccessTokenForFile(File $file)
    {
        $token = $this->findAccessTokenForFile($file);

        if (!$token) {
            return true;
        } else {
            $newEncryptionKey = $this->user->getKey();
            $result = $this->changeEncryptionKeyForFile($file, $newEncryptionKey, self::ENCRYPTION_PERSONAL);

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

    /**
     * @param File $file
     * @return bool|string
     */
    public function flipTokenActiveState(File $file)
    {
        $token = $this->findAccessTokenForFile($file);

        if ($token) {
            if ($token->getActiveState() === FileToken::STATE_OPEN) {
                $newState = FileToken::STATE_RESTRICTED;
            } else {
                $newState = FileToken::STATE_OPEN;
            }

            $updateStatement = $this->pdo->prepare("UPDATE share SET active = :state WHERE id = :tokenId");
            $updateStatement->bindValue(':state', $newState);
            $updateStatement->bindValue(':tokenId', $token->getId());

            if ($updateStatement->execute()) {
                return $newState;
            }
        }

        return false;
    }

    /**
     * @param File $file
     * @param string $password
     * @return bool
     */
    public function setTokenPasswordForFile(File $file, string $password)
    {
        $token = $this->findAccessTokenForFile($file);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if ($token && $token->getActiveState() === FileToken::STATE_RESTRICTED) {
            $updateStatement = $this->pdo->prepare("UPDATE share SET password = :password WHERE id = :tokenId");
            $updateStatement->bindValue(':password', $hashedPassword);
            $updateStatement->bindValue(':tokenId', $token->getId());

            if ($updateStatement->execute()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param File $file
     * @return bool
     */
    public function clearTokenPasswordForFile(File $file)
    {
        $token = $this->findAccessTokenForFile($file);

        if ($token && !empty($token->getPasswordHash())) {
            $updateStatement = $this->pdo->prepare("UPDATE share SET password = NULL WHERE id = :tokenId");
            $updateStatement->bindValue(':tokenId', $token->getId());

            if ($updateStatement->execute()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function generateToken()
    {
        return sha1(random_bytes(32));
    }

    private function changeEncryptionKeyForFile(File $file, string $newEncryptionKey, string $encryptionType) : bool
    {
        $currentEncryptionKey = $this->getEncryptionKeyForFile($file);
        $this->encryptionService->setKey($currentEncryptionKey);

        $pathToPlaintext = $this->encryptionService->decryptFile($file);

        if ($pathToPlaintext) {
            $this->encryptionService->setKey($newEncryptionKey);
            if ($this->encryptionService->encryptFile($file, $pathToPlaintext)) {
                @unlink($pathToPlaintext);

                $updateStatement = $this->pdo->prepare("UPDATE files SET encryption = :encryption WHERE id = :id");
                $updateStatement->bindValue(':encryption', $encryptionType);
                $updateStatement->bindValue(':id', $file->getId());
                $updateStatement->execute();

                return true;
            }
        }

        return false;
    }
}
