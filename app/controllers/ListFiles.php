<?php

namespace controllers;

use lib\DataTypes\File;
use lib\Repositories\FileRepository;
use lib\Services\SearchService;
use lib\Services\SortService;
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
        $this->fileRepository = $this->getFileRepository();
    }

    public function index()
    {
        $sort = $this->getContainer()->get(SortService::class);
        $currentSorting = $sort->getSortBy();
        return $this->parseView('main', ['currentSorting' => $currentSorting, 'config' => $this->config()]);
    }

    public function actionList()
    {
        $location = $this->param('location');

        $fileList = $this->fileRepository->findByLocation($this->authentication()->getUser(), $location);

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
        $search = $this->getContainer()->get(SearchService::class);

        $fileList = $search->search($this->authentication()->getUser(), $searchString);

        if ($fileList === false) {
            return $this->parseView('partials/nofiles', ['message' => Translation::getInstance()->translate('NO_CRITERIA_SEARCH')]);
        } else if (count($fileList)) {
            return $this->parseView('partials/filelist', ['fileList' => $fileList]);
        }
        else {
            return $this->parseView('partials/nofiles', ['message' => Translation::getInstance()->translate('NO_FILES_SEARCH')]);
        }
    }

    public function actionImages()
    {
        $location = $this->param('location');

        $imageFileExts = $this->config()->find('type_groups', 'image');
        $fileList = $this->fileRepository->findByFileExtension($this->authentication()->getUser(), $location, $imageFileExts);
        $images = [];

        $phpExtension = $this->config()->find('files', 'skip_dl_php_extension') ? '' : '.php';

        /** @var File $file */
        foreach ($fileList as $file) {
            $images[] = [
                'src' => AFILE_LOCATION . 'dl' . $phpExtension . '/' . $file->getStringId(),
                'subHtml' => $file->getName()
            ];
        }

        return $this->outputJSON($images);
    }
}
