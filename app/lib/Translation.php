<?php

namespace lib;

class Translation {
    private $language;
    private $languageData;

    public function __construct($language = 'en') {
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
}
