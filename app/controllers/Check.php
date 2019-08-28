<?php

namespace controllers;

use lib\Translation;

class Check extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_OPEN;
    }

    public function index() {
        $info = [
            'title' => $this->config()->get('title') ?? 'aFile'
        ];

        $user = $this->authentication()->getUser();

        if ($user) {
            $info['login'] = true;
            $info['user'] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'type' => $user->getType()
            ];
            $info['language'] = Translation::getInstance()->getLanguageData();
            $info['skip_dl_php_extension'] = $this->config()->get('files', 'skip_dl_php_extension');
        }
        else {
            $info['login'] = false;
        }

        return $this->outputJSON($info);
    }
}
