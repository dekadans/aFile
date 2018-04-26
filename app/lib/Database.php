<?php

namespace lib;

class Database {
    /** @var \PDO */
    private $pdo;
    /** @var string */
    private $driver = '';
    /** @var \stdClass */
    private $config;

    private function __construct()
    {
        $this->config = Config::getInstance()->database;
        $this->driver = $this->config->driver;

        switch ($this->driver) {
            case 'mysql':
                $this->connectToMysql();
                break;
            default:
                throw new \Exception("Unsupported database driver", 1);
        }

        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    private function connectToMysql()
    {
        $host = $this->config->host;
        $db = $this->config->database;
        $user = $this->config->user;
        $password = $this->config->password;

        $this->pdo = new \PDO('mysql:host='. $host .';dbname='. $db, $user, $password);
    }

    public function getPDO()
    {
        return $this->pdo;
    }

    /** @var Database */
    private static $instance;

    /**
     * @return Database
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
