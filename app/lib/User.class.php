<?php

namespace app\lib;

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
                $enckey = base64_decode($user['encryption_key']);
                $encrypt = new Encryption($password);
                $this->encryption_key = $encrypt->decrypt($enckey);
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

            $key = self::generateEncryptionKey();
            $encrypt = new Encryption($password);
            $keyenc = base64_encode($encrypt->encrypt($key));

            $addUser = Registry::get('db')->getPDO()->prepare('INSERT INTO users (username, password, encryption_key) VALUES (?,?,?)');

            try {
                if ($addUser->execute([$username, $pwhash, $keyenc])) {
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
     * Creates a new 32 characters long encryption key.
     * @return string
     */
    protected static function generateEncryptionKey() {
        $letters = array_merge(range('A','Z'),range('a','z'),range(0,9));
        $key = '';
        while (strlen($key) < 32)
        {
            $key .= $letters[rand(0,count($letters)-1)];
        }

        return $key;
    }
}