<?php

namespace lib\DataTypes;

class DatabaseConfiguration
{
    private $host;
    private $database;
    private $username;
    private $password;

    public function __construct(string $host, string $database, string $username, string $password)
    {
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;
    }

    public function isInstalled()
    {
        return !(is_null($this->host) || $this->host === 'DB_HOST');
    }

    public function getDSN()
    {
        return 'mysql:host='. $this->host .';dbname='. $this->database;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}