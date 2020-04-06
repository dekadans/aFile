<?php

namespace lib\DataTypes;

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
            $this->encryption_key = $userData['encryption_key'];
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
}
