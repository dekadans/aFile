<?php
namespace controllers;

use lib\Services\CreateFileService;
use lib\Translation;

class Create extends AbstractController
{
    /** @var string */
    private $name;

    /** @var string */
    private $location;

    /** @var CreateFileService */
    private $createFileService;

    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function init()
    {
        $this->name = $this->param('name');
        $this->location = $this->param('location');

        $this->createFileService = $this->getContainer()->get(CreateFileService::class);

        if (empty($this->name)) {
            return $this->outputJSON([
                'error' => Translation::getInstance()->translate('FAILED')
            ]);
        }

        if ($this->getFileRepository()->exists($this->authentication()->getUser(), $this->name, $this->location)) {
            return $this->outputJSON([
                'error' => Translation::getInstance()->translate('FILE_EXISTS')
            ]);
        }
    }

    public function actionDirectory()
    {
        $result = $this->createFileService->createDirectory($this->authentication()->getUser(), $this->name, $this->location);

        if ($result) {
            return $this->outputJSON(['status' => 'ok']);
        }

        return $this->outputJSON([
            'error' => Translation::getInstance()->translate('CREATE_DIRECTORY_FAILED')
        ]);
    }

    public function actionLink()
    {
        $url = $this->param('url');

        if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            $result = $this->createFileService->createLink($this->authentication()->getUser(), $this->name, $this->location, $url);

            if ($result) {
                return $this->outputJSON(['status' => 'ok']);
            }
        }

        return $this->outputJSON(['status' => 'error', 'error' => Translation::getInstance()->translate('LINK_ERROR')]);
    }

    public function actionFile()
    {
        $file = $this->createFileService->createFile($this->authentication()->getUser(), $this->name, $this->location, 'text/plain');

        if ($file) {
            return $this->outputJSON(['status' => 'ok']);
        }

        return $this->outputJSON(['status' => 'error', 'error' => Translation::getInstance()->translate('EDITOR_CREATE_ERROR')]);
    }
}