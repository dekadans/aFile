<?php

namespace lib;

use lib\DataTypes\DatabaseConfiguration;

class Database {
    /** @var DatabaseConfiguration */
    private $config;

    /** @var \PDO|null */
    private $pdo = null;

    public function __construct(DatabaseConfiguration $config)
    {
        $this->config = $config;

        if ($this->config->isInstalled()) {
            $this->pdo = new \PDO($config->getDSN(), $this->config->getUsername(), $this->config->getPassword());
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        }
    }

    public function getPDO()
    {
        return $this->pdo;
    }

    public function getConfiguration() : DatabaseConfiguration
    {
        return $this->config;
    }
}
