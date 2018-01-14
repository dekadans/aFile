<?php

namespace lib;

class Translation {
    private static $language;
    private static $languageData;

    public static function loadLanguage($language = 'en') {
        $languagePath = __DIR__ . '/../../config/' . $language . '.json';
        $languageFile = file_get_contents($languagePath);
        $languageFile = json_decode($languageFile, true);
        self::$languageData = $languageFile;
    }

    public static function getLanguage() {
        return self::$language;
    }

    public static function getLanguageData() {
        return self::$languageData;
    }

    public static function translate($code) {
        if (isset(self::$languageData[$code])) {
            return self::$languageData[$code];
        }
        else {
            return $code;
        }
    }
}
