<?php

namespace controllers;

use \lib\Registry;

class Check extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_OPEN;
    }

    public function index() {
        $info = [];

        if (Registry::get('user')) {
            $info['login'] = true;
            $info['user'] = [
                'id' => Registry::get('user')->getId(),
                'username' => Registry::get('user')->getUsername(),
                'type' => Registry::get('user')->getType()
            ];
        }
        else {
            $info['login'] = false;
        }

        $info['siprefix'] = Registry::get('config')->presentation->siprefix;

        $languagePath = __DIR__ . '/../../config/' . Registry::get('config')->language . '.json';
        $languageFile = file_get_contents($languagePath);
        $languageFile = json_decode($languageFile, true);

        $info['language'] = $languageFile;

        $this->outputJSON($info);
    }
}
