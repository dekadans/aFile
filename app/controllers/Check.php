<?php

namespace controllers;

use lib\Authentication;
use lib\Translation;

class Check extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_OPEN;
    }

    public function index() {
        $info = [];

        $user = Authentication::getUser();

        if ($user) {
            $info['login'] = true;
            $info['user'] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'type' => $user->getType()
            ];
            $info['language'] = Translation::getInstance()->getLanguageData();
        }
        else {
            $info['login'] = false;
        }

        return $this->outputJSON($info);
    }
}
