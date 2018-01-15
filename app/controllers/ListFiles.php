<?php

namespace controllers;

use lib\FileList;
use lib\Registry;

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

        $fileList = new FileList(Registry::get('user'), $location);
        $this->parseView('partials/filelist', ['fileList' => $fileList]);
    }
}
