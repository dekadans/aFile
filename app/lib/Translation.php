<?php

namespace lib;

class Translation {
    private $language;
    private $languageData;

    private function __construct($language) {
        $languagePath = __DIR__ . '/../../config/' . $language . '.json';
        $languageFile = file_get_contents($languagePath);
        $languageFile = json_decode($languageFile, true);
        $this->languageData = $languageFile;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function getLanguageData() {
        return $this->languageData;
    }

    public function translate($code) {
        if (isset($this->languageData[$code])) {
            return $this->languageData[$code];
        }
        else {
            return $code;
        }
    }

    public static function loadLanguage($language = 'en')
    {
        self::$instance = new self($language);
    }

    /** @var Translation */
    private static $instance;

    /**
     * @return Translation
     */
    public static function getInstance()
    {
        return self::$instance;
    }
}
