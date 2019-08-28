<?php

namespace controllers;

use lib\Translation;
use mysql_xdevapi\Exception;

class Check extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_OPEN;
    }

    public function index() {
        $info = [
            'title' => $this->config()->find('title') ?? 'aFile',
            'language' => Translation::getInstance()->getLanguageData()
        ];

        $user = $this->authentication()->getUser();

        if ($user) {
            $info['login'] = true;
            $info['user'] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'type' => $user->getType()
            ];
            $info['skip_dl_php_extension'] = $this->config()->find('files', 'skip_dl_php_extension');
        }
        else {
            $info['login'] = false;
        }

        return $this->outputJSON($info);
    }
}
