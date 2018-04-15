<?php

namespace lib;

class Database {
    /** @var \PDO */
    protected $pdo;

    public function __construct()
    {
        switch (Registry::get('config')->database->driver) {
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
        $host = Registry::get('config')->database->host;
        $db = Registry::get('config')->database->database;
        $user = Registry::get('config')->database->user;
        $password = Registry::get('config')->database->password;

        $this->pdo = new \PDO('mysql:host='. $host .';dbname='. $db, $user, $password);
    }

    public function getPDO()
    {
        return $this->pdo;
    }
}
