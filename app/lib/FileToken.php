<?php
namespace lib;


use Defuse\Crypto\Key;
use lib\Repositories\FileRepository;

class FileToken
{
    const STATE_OPEN = 'OPEN';
    const STATE_PASSWORD = 'PASSWORD';
    const STATE_BOTH = 'BOTH';

    /**
     * @var int
     */
    private $id;

    /**
     * @var File
     */
    private $file;
    /**
     * @var string
     */
    private $openToken;
    /**
     * @var string
     */
    private $passwordToken;
    /**
     * @var string
     */
    private $active;
    /**
     * @var string
     */
    private $password;
    /**
     * @var string
     */
    private $encryptionKey;
    /**
     * @var string
     */
    private $activeTo;

    /**
     * FileToken constructor.
     * @param int $id
     * @param int $fileId
     * @param string $openToken
     * @param string $passwordToken
     * @param string $active
     * @param string $password
     * @param string $encryptionKey
     * @param string $activeTo
     */
    private function __construct($id, int $fileId, $openToken, $passwordToken, $active, $password, $encryptionKey, $activeTo)
    {
        $fileRepository = new FileRepository();

        $this->id = $id;
        $this->file = $fileRepository->find($fileId);
        $this->openToken = $openToken;
        $this->passwordToken = $passwordToken;
        $this->active = $active;
        $this->password = $password;
        $this->encryptionKey = $encryptionKey;
        $this->activeTo = $activeTo;
    }

    public function exists()
    {
        return !is_null($this->id);
    }

    public function destroy()
    {
        $newEncryptionKey = Singletons::$auth->getUser()->getKey();
        $result = $this->file->changeEncryptionKey($newEncryptionKey, File::ENCRYPTION_PERSONAL);

        if ($result) {
            $SQL = "DELETE from share WHERE id = :id";
            $deleteStatement = Database::getInstance()->getPDO()->prepare($SQL);
            $deleteStatement->bindParam(':id', $this->id);
            return $deleteStatement->execute();
        }
    }

    /**
     * @param string $validUntil
     * @return bool
     */
    public function enableOpen($validUntil = null)
    {
        $activeState = $this->activateState(self::STATE_OPEN);

        $token = $this->getOpenToken(true);
        $tokenSettings = ['open_token' => $token];

        if ($this->exists()) {
            return $this->update($activeState, $tokenSettings, $validUntil);
        }
        else {
            return $this->create($activeState, $tokenSettings, $validUntil);
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

        $token = $this->getPasswordToken(true);
        $tokenSettings = ['password_token' => $token];

        if ($this->exists()) {
            return $this->update($activeState, $tokenSettings, $validUntil, $hashedPassword);
        }
        else {
            return $this->create($activeState, $tokenSettings, $validUntil, $hashedPassword);
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

        $createStatement = Database::getInstance()->getPDO()->prepare($SQL);
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
        $openToken = $token['open_token'] ?? $this->getOpenToken();
        $passwordToken = $token['password_token'] ?? $this->getPasswordToken();
        $password = $password ?? $this->getPassword();

        $updateStatement = Database::getInstance()->getPDO()->prepare($SQL);
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
        if ($this->exists()) {
            if ($this->getActiveState() === $state) {
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
     * @param bool $generateIfMissing
     * @return null|string
     */
    public function getOpenToken(bool $generateIfMissing = false)
    {
        if (isset($this->openToken)) {
            return $this->openToken;
        }
        else {
            if ($generateIfMissing) {
                $this->openToken = $this->generateToken();
                return $this->openToken;
            }
            else {
                return null;
            }
        }
    }

    /**
     * @param bool $generateIfMissing
     * @return null|string
     */
    public function getPasswordToken(bool $generateIfMissing = false)
    {
        if (isset($this->passwordToken)) {
            return $this->passwordToken;
        }
        else {
            if ($generateIfMissing) {
                $this->passwordToken = $this->generateToken();
                return $this->passwordToken;
            }
            else {
                return null;
            }
        }
    }

    /**
     * @return string
     */
    private function generateToken()
    {
        return sha1(random_bytes(32));
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getActiveState()
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getEncryptionKey()
    {
        return $this->encryptionKey;
    }

    /**
     * @param array $data
     * @return FileToken
     */
    public static function createFromArray(array $data)
    {
        return new self(
            $data['id'],
            $data['file_id'],
            $data['open_token'] ?? null,
            $data['password_token'] ?? null,
            $data['active'] ?? null,
            $data['password'] ?? null,
            $data['encryption_key'] ?? null,
            $data['active_to'] ?? null
        );
    }
}