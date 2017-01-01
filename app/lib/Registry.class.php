<?php

namespace lib;

class Registry {
    public static $storage;

    public static function set($key, $value) {
        if (!is_array(self::$storage)) {
            self::$storage = [];
        }
        self::$storage[$key] = $value;
    }

    public static function get($key) {
        if (is_array(self::$storage) && isset(self::$storage[$key])) {
            return self::$storage[$key];
        }
    }
}
