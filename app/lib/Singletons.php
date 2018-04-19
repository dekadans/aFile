<?php

namespace lib;

class Singletons {
    private static $storage;

    /** @var Translation */
    public static $language;

    /** @var Authentication */
    public static $auth;

    /** @var Database */
    public static $db;

    /** @var Sort */
    public static $sort;

    public static function set($key, $value) {
        if (!is_array(self::$storage)) {
            self::$storage = [];
        }
        self::$storage[$key] = $value;
    }

    /**
     * @param $key
     * @return mixed
     */
    public static function get($key) {
        if (is_array(self::$storage) && isset(self::$storage[$key])) {
            return self::$storage[$key];
        }
    }
}
