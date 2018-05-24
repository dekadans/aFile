<?php

namespace controllers;

use lib\Authentication;
use lib\Repositories\FileRepository;
use lib\Sort;
use lib\Translation;

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
        return $this->parseView('main', ['currentSorting' => $currentSorting]);
    }

    public function actionList()
    {
        $location = $this->param('location');

        if (empty($location)) {
            $location = null;
        }

        $fileList = $this->fileRepository->findByLocation(Authentication::getUser(), $location);

        if (count($fileList)) {
            return $this->parseView('partials/filelist', ['fileList' => $fileList]);
        }
        else {
            return $this->parseView('partials/nofiles', ['message' => Translation::getInstance()->translate('NO_FILES')]);
        }

    }

    public function actionSearch()
    {
        $searchString = $this->param('search');

        $fileList = $this->fileRepository->search(Authentication::getUser(), $searchString);
        if (count($fileList)) {
            return $this->parseView('partials/filelist', ['fileList' => $fileList]);
        }
        else {
            return $this->parseView('partials/nofiles', ['message' => Translation::getInstance()->translate('NO_FILES_SEARCH')]);
        }
    }
}
