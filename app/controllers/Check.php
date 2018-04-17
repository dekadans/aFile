<?php

namespace controllers;

use \lib\Singletons;

class Check extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_OPEN;
    }

    public function index() {
        $info = [];

        $user = Singletons::$auth->getUser();

        if ($user) {
            $info['login'] = true;
            $info['user'] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'type' => $user->getType()
            ];
            $info['language'] = Singletons::$language->getLanguageData();
        }
        else {
            $info['login'] = false;
        }

        $this->outputJSON($info);
    }
}
