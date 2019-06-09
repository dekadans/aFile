<?php

namespace controllers;

use lib\Authentication;
use lib\Config;
use lib\DataTypes\File;
use lib\HTTP\Response;
use lib\Repositories\FileRepository;
use lib\Search;
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
        $search = new Search(Authentication::getUser(), $this->fileRepository);

        $fileList = $search->search($searchString);

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

        $imageFileExts = Config::getInstance()->type_groups->image;
        $fileList = $this->fileRepository->findByFileExtension(Authentication::getUser(), $location, $imageFileExts);
        $images = [];

        /** @var File $file */
        foreach ($fileList as $file) {
            $images[] = [
                'src' => AFILE_LOCATION . 'dl' . (Config::getInstance()->files->skip_dl_php_extension ? '' : '.php') . '/' . $file->getStringId(),
                'subHtml' => $file->getName()
            ];
        }

        return $this->outputJSON($images);
    }
}
