<?php

namespace lib;

class User {
    private $id;
    private $username;
    private $encryption_key;
    private $account_type;
    private $hashedPassword;

    public function __construct(array $userData) {
        if (!empty($userData)) {
            $this->id = $userData['id'];
            $this->username = $userData['username'];
            $this->account_type = $userData['account_type'];
            $this->hashedPassword = $userData['password'];
        }
        else {
            $this->id = '0';
        }
    }

    public function isset()
    {
        return $this->id !== '0';
    }

    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getKey() {
        return $this->encryption_key;
    }

    public function getType() {
        return $this->account_type;
    }

    public function setKey($key) {
        $this->encryption_key = $key;
    }

    /**
     * @return string
     */
    public function getHashedPassword()
    {
        return $this->hashedPassword;
    }

    /**
     * Creates a new user, unless the username already exists.
     * @param string $username
     * @param string $password
     *
     * @return User | boolean
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public static function create($username, $password) {
        $checkName = Database::getInstance()->getPDO()->prepare('SELECT * FROM users WHERE username = ?');
        $checkName->execute([$username]);

        if (!$checkName->fetch()) {
            $pwhash = password_hash($password, PASSWORD_DEFAULT);

            $key = \Defuse\Crypto\KeyProtectedByPassword::createRandomPasswordProtectedKey($password);
            $keyAscii = $key->saveToAsciiSafeString();

            $addUser = Database::getInstance()->getPDO()->prepare('INSERT INTO users (username, password, encryption_key) VALUES (?,?,?)');

            try {
                if ($addUser->execute([$username, $pwhash, $keyAscii])) {
                    return new self(Database::getInstance()->getPDO()->lastInsertId());
                }
                else {
                    return false;
                }
            }
            catch (\PDOException $e) {
                return false;
            }
        }
        else {
            return false;
        }
    }
}
