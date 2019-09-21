<?php

namespace lib\Repositories;

class TranslationRepository {
    private $language;
    private $languageData;

    public function __construct($language) {
        $languagePath = __DIR__ . '/../../../config/' . $language . '.json';
        $languageFile = file_get_contents($languagePath);
        $languageFile = json_decode($languageFile, true);
        $this->language = $language;
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
