<?php

namespace app\controllers;

class ListFiles extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        $location = $this->param('location');

        if (!$location) {
            $location = base64_encode('/');
        }

        $list = new \app\lib\FileList(\app\lib\Registry::get('user'), $location);
        $this->outputJSON($list->run());
    }
}
