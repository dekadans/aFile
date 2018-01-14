<?php

namespace controllers;

class ListFiles extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        $this->parseView('main');
    }

    public function actionList() {
        $location = $this->param('location');

        if (!$location) {
            $location = base64_encode('/');
        }

        $list = new \lib\FileList(\lib\Registry::get('user'), $location);
        $this->outputJSON($list->run());
    }
}
