<?php

namespace lib;

class User {
    protected $id;
    protected $username;
    protected $encryption_key;
    protected $account_type;

    public function __construct($id, $password = false) {
        $user = $this->getUserFromDb($id);

        if ($user) {
            $this->id = $user['id'];
            $this->username = $user['username'];
            $this->account_type = $user['account_type'];

            if ($password) {
                $protectedKey = \Defuse\Crypto\KeyProtectedByPassword::loadFromAsciiSafeString($user['encryption_key']);
                $key = $protectedKey->unlockKey($password);
                $this->encryption_key = $key->saveToAsciiSafeString();
            }
        }
        else {
            $this->id = '0';
        }
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
     * Retrieve the user's info from the database.
     * @param int $id
     * @return array | boolean
     */
    protected function getUserFromDb($id) {
        $getUser = Registry::get('db')->getPDO()->prepare('SELECT * FROM users WHERE id = ?');
        $getUser->execute([$id]);
        $userRow = $getUser->fetch();

        return $userRow;
    }

    /**
     * Authenticate a user against the database.
     * @param string $username
     * @param string $password
     *
     * @return \app\lib\User | boolean
     */
    public static function authenticate($username, $password) {
        $checkName = Registry::get('db')->getPDO()->prepare('SELECT * FROM users WHERE username = ?');
        $checkName->execute([$username]);
        $userRow = $checkName->fetch();

        if ($userRow) {
            if (password_verify($password, $userRow['password'])) {
                return new self($userRow['id'], $password);
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    /**
     * Creates a new user, unless the username already exists.
     * @param string $username
     * @param string $password
     *
     * @return \app\lib\User | boolean
     */
    public static function create($username, $password) {
        $checkName = Registry::get('db')->getPDO()->prepare('SELECT * FROM users WHERE username = ?');
        $checkName->execute([$username]);

        if (!$checkName->fetch()) {
            $pwhash = password_hash($password, PASSWORD_DEFAULT);

            $key = \Defuse\Crypto\KeyProtectedByPassword::createRandomPasswordProtectedKey($password);
            $keyAscii = $key->saveToAsciiSafeString();

            $addUser = Registry::get('db')->getPDO()->prepare('INSERT INTO users (username, password, encryption_key) VALUES (?,?,?)');

            try {
                if ($addUser->execute([$username, $pwhash, $keyAscii])) {
                    return new self(Registry::get('db')->getPDO()->lastInsertId());
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

    /**
     * Return instance of User by unique username
     * @param  string $username
     * @return User
     */
    public static function getByUsername($username) {
        $checkName = Registry::get('db')->getPDO()->prepare('SELECT id FROM users WHERE username = ?');
        $checkName->execute([$username]);
        $userRow = $checkName->fetch();

        if ($userRow) {
            return new self($userRow['id']);
        }

        return false;
    }
}
