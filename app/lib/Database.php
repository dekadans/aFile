<?php

namespace lib;

class Database {
    /** @var \PDO */
    protected $pdo;

    public function __construct()
    {
        switch (Singletons::get('config')->database->driver) {
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
        $host = Singletons::get('config')->database->host;
        $db = Singletons::get('config')->database->database;
        $user = Singletons::get('config')->database->user;
        $password = Singletons::get('config')->database->password;

        $this->pdo = new \PDO('mysql:host='. $host .';dbname='. $db, $user, $password);
    }

    public function getPDO()
    {
        return $this->pdo;
    }
}
