<?php
namespace lib;

class Installation
{
    public static function isInstalled()
    {
        return file_exists(__DIR__ . '/../../config/config.ini');
    }

    public function tryAndConnectToDatabase(string $host, string $database, string $username, string $password)
    {
        $config = Config::getInstance();
        $config->database->host = $host;
        $config->database->database = $database;
        $config->database->user = $username;
        $config->database->password = $password;

        try {
            $database = Database::getInstance();
            return true;
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }

    public function createTables()
    {
        $pathToQueries = __DIR__ . '/../../' . Config::getInstance()->database->installation;

        if (!file_exists($pathToQueries)) {
            throw new \Exception('Could not find queries for creating tables.');
        }

        $sqlQueries = require_once $pathToQueries;

        foreach ($sqlQueries as $query) {
            Database::getInstance()->getPDO()->exec($query);
        }
    }

    public function writeConfig(array $replaceMatrix)
    {
        $config = file_get_contents(__DIR__ . '/../../config/config.ini.template');

        if ($config === false || empty($config)) {
            return false;
        }

        $config = strtr($config, $replaceMatrix);
        return file_put_contents(__DIR__ . '/../../config/config.ini', $config);
    }
}
