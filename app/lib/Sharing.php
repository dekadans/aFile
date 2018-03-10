<?php
/**
 * Created by PhpStorm.
 * User: Tomas
 * Date: 2018-01-22
 * Time: 19:51
 */

namespace lib;


use Defuse\Crypto\Key;

class Sharing
{
    const STATE_OPEN = 'OPEN';
    const STATE_PASSWORD = 'PASSWORD';
    const STATE_BOTH = 'BOTH';

    /**
     * @var File
     */
    protected $file;

    /**
     * @var array
     */
    protected $sharingInfo = [];

    /**
     * Sharing constructor.
     * @param File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
        $this->sharingInfo = $this->file->getSharingInfo();
    }

    /**
     * @param string $validUntil
     * @return bool
     */
    public function enableOpen($validUntil = null)
    {
        $activeState = $this->activateState(self::STATE_OPEN);

        if (empty($this->sharingInfo)) {
            $newToken = $this->generateToken();
            $tokenSettings = ['open_token' => $newToken];
            return $this->create($activeState, $tokenSettings, $validUntil);
        }
        else {
            $token = $this->sharingInfo['open_token'] ?? $this->generateToken();
            $tokenSettings = ['open_token' => $token];
            return $this->update($activeState, $tokenSettings, $validUntil);
        }
    }

    /**
     * @param string $password
     * @param string $validUntil
     * @return bool
     */
    public function enablePassword($password, $validUntil = null)
    {
        $activeState = $this->activateState(self::STATE_PASSWORD);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if (empty($this->sharingInfo)) {
            $newToken = $this->generateToken();
            $tokenSettings = ['password_token' => $newToken];
            return $this->create($activeState, $tokenSettings, $validUntil, $hashedPassword);
        }
        else {
            $token = $this->sharingInfo['password_token'] ?? $this->generateToken();
            $tokenSettings = ['password_token' => $token];
            return $this->update($activeState, $tokenSettings, $validUntil, $hashedPassword);
        }
    }

    /**
     * @param string $state
     * @param array $token
     * @param string $validUntil
     * @param string $password
     * @return bool
     */
    private function create($state, $token = [], $validUntil = null, $password = null) {
        $encryptionKey = Key::createNewRandomKey();
        $keyAscii = $encryptionKey->saveToAsciiSafeString();

        $SQL = "INSERT INTO share (file_id, open_token, password_token, active, password, encryption_key, active_to)
                    VALUES (
                      :fileId,
                      :openToken,
                      :passwordToken,
                      :active,
                      :password,
                      :encryptionKey,
                      :activeTo
                    )";

        $id = $this->file->getId();
        $openToken = $token['open_token'] ?? null;
        $passwordToken = $token['password_token'] ?? null;

        $createStatement = Registry::get('db')->getPDO()->prepare($SQL);
        $createStatement->bindParam(':fileId', $id);
        $createStatement->bindParam(':openToken', $openToken);
        $createStatement->bindParam(':passwordToken', $passwordToken);
        $createStatement->bindParam(':active', $state);
        $createStatement->bindParam(':password', $password);
        $createStatement->bindParam(':encryptionKey', $keyAscii);
        $createStatement->bindParam(':activeTo', $validUntil);

        if ($createStatement->execute()) {
            return $this->file->changeEncryptionKey($keyAscii, File::ENCRYPTION_TOKEN);
        }

        return false;
    }

    /**
     * @param string $state
     * @param array $token
     * @param string $validUntil
     * @param string $password
     * @return bool
     */
    private function update($state, $token = [], $validUntil = null, $password = null)
    {
        $SQL = "UPDATE share SET
                open_token = :openToken,
                password_token = :passwordToken,
                active = :active,
                password = :password,
                active_to = :activeTo
                WHERE file_id = :fileId";

        $id = $this->file->getId();
        $openToken = $token['open_token'] ?? $this->sharingInfo['open_token'];
        $passwordToken = $token['password_token'] ?? $this->sharingInfo['password_token'];
        $password = $password ?? $this->sharingInfo['password'];

        $updateStatement = Registry::get('db')->getPDO()->prepare($SQL);
        $updateStatement->bindParam(':fileId', $id);
        $updateStatement->bindParam(':openToken', $openToken);
        $updateStatement->bindParam(':passwordToken', $passwordToken);
        $updateStatement->bindParam(':active', $state);
        $updateStatement->bindParam(':password', $password);
        $updateStatement->bindParam(':activeTo', $validUntil);

        return $updateStatement->execute();
    }

    /**
     * @param string $state
     * @return string
     */
    private function activateState($state)
    {
        if (!empty($this->sharingInfo)) {
            if ($this->sharingInfo['active'] === $state) {
                return $state;
            }
            else {
                return self::STATE_BOTH;
            }
        }
        else {
            return $state;
        }
    }

    /**
     * @return string
     */
    private function generateToken()
    {
        return sha1(random_bytes(32));
    }
}