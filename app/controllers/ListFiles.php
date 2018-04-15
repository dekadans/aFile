<?php

namespace controllers;

use lib\FileRepository;
use lib\Registry;

class ListFiles extends AbstractController {
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function index()
    {
        $this->parseView('main');
    }

    public function actionList()
    {
        $location = $this->param('location');

        if (!$location) {
            $location = base64_encode('/');
        }

        $fileList = FileRepository::findByLocation(Registry::get('user'), $location);
        $this->parseView('partials/filelist', ['fileList' => $fileList, 'printPath' => false]);
    }

    public function actionSearch()
    {
        $searchString = $this->param('search');

        $fileList = FileRepository::search(Registry::get('user'), $searchString);
        $this->parseView('partials/filelist', ['fileList' => $fileList, 'printPath' => true]);
    }
}
