<?php
namespace lib\Repositories;

use lib\DataTypes\DatabaseConfiguration;

class ConfigurationRepository {
    private $config;

    public function __construct ($filename) {
        $this->config = parse_ini_file($filename,true);
    }

    public function find(string $section, string $property = '')
    {
        if (is_array($this->config[$section]) && isset($this->config[$section][$property])) {
            return $this->config[$section][$property];
        } else if (!is_array($this->config[$section]) && !empty($this->config[$section])) {
            return $this->config[$section];
        } else {
            return null;
        }
    }

    public function getDatabaseConfiguration()
    {
        return new DatabaseConfiguration(
            $this->config['database']['host'],
            $this->config['database']['database'],
            $this->config['database']['user'],
            $this->config['database']['password']
        );
    }
}
