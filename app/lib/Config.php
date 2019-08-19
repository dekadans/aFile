<?php
namespace lib;

use lib\DataTypes\DatabaseConfiguration;

class Config {
    private $config;

    private function __construct ($filename) {
        $this->config = json_decode(json_encode(parse_ini_file($filename,true)));
    }

    function __get ($property) {
        if (isset($this->config->{$property})) {
            return $this->config->{$property};
        }
        else {
            return null;
        }
    }

    public function getDatabaseConfiguration()
    {
        return new DatabaseConfiguration(
            $this->config->database->host,
            $this->config->database->database,
            $this->config->database->user,
            $this->config->database->password
        );
    }

    public static function load($filename)
    {
        self::$instance = new self($filename);
    }

    /** @var Config */
    private static $instance;

    /**
     * @return Config
     */
    public static function getInstance()
    {
        return self::$instance;
    }
}
