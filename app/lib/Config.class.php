<?php
namespace app\lib;

class Config {
    protected $config;

    function __construct ($filename) {
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
}
