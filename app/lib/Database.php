<?php

namespace lib;

use lib\DataTypes\DatabaseConfiguration;

class Database {
    /** @var \PDO|null */
    private $pdo = null;

    public function __construct(DatabaseConfiguration $config)
    {
        if ($config->isInstalled()) {
            $this->pdo = new \PDO($config->getDSN(), $config->getUsername(), $config->getPassword());
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }
    }

    public function getPDO()
    {
        return $this->pdo;
    }
}
