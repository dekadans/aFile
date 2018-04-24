<?php

namespace controllers;

use lib\Repositories\FileRepository;
use lib\Singletons;
use lib\Sort;

class ListFiles extends AbstractController {
    /** @var FileRepository */
    private $fileRepository;

    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function init()
    {
        $this->fileRepository = new FileRepository();
    }

    public function index()
    {
        $currentSorting = Sort::getInstance()->getSortBy();
        $this->parseView('main', ['currentSorting' => $currentSorting]);
    }

    public function actionList()
    {
        $location = $this->param('location');

        if (empty($location)) {
            $location = null;
        }

        $fileList = $this->fileRepository->findByLocation(Singletons::$auth->getUser(), $location);
        $this->parseView('partials/filelist', ['fileList' => $fileList, 'printPath' => false]);
    }

    public function actionSearch()
    {
        $searchString = $this->param('search');

        $fileList = $this->fileRepository->search(Singletons::$auth->getUser(), $searchString);
        $this->parseView('partials/filelist', ['fileList' => $fileList, 'printPath' => true]);
    }
}
