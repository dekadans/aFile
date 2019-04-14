<?php
namespace lib\DataTypes;

class FileToken
{
    const STATE_OPEN = 'OPEN';
    const STATE_RESTRICTED = 'RESTRICTED';
    const STATE_NONE = 'NONE';

    /** @var int */
    private $id;
    /** @var int */
    private $fileId;
    /** @var string */
    private $token;
    /** @var string */
    private $active;
    /** @var string */
    private $password;
    /** @var string */
    private $encryptionKey;

    /**
     * FileToken constructor.
     * @param int $id
     * @param int $fileId
     * @param string $openToken
     * @param string $active
     * @param $password
     * @param string $encryptionKey
     */
    private function __construct($id, int $fileId, $openToken, $active, $password, $encryptionKey)
    {
        $this->id = $id;
        $this->fileId = $fileId;
        $this->token = $openToken;
        $this->active = $active;
        $this->password = $password;
        $this->encryptionKey = $encryptionKey;
    }

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function getPasswordHash()
    {
        return $this->password;
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
            $data['active'] ?? null,
            $data['password'] ?? null,
            $data['encryption_key'] ?? null
        );
    }
}