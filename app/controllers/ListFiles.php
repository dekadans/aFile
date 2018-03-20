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
        $this->parseView('partials/filelist', ['fileList' => $fileList]);
    }

    public function actionSearch()
    {
        $searchString = $this->param('search');
        $type = '';

        if (preg_match('/type:([A-Za-z]*)/', $searchString, $matches)) {
            $type = $matches[1];

            $searchString = trim(preg_replace('/type:[A-Za-z]*/', '', $searchString));
        }

        $fileList = FileRepository::findBySearchParameters(Registry::get('user'), $searchString, $type);
        $this->parseView('partials/filelist', ['fileList' => $fileList, 'printPath' => true]);
    }
}
