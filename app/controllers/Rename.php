<?php
namespace controllers;

use lib\Repositories\FileRepository;
use lib\Translation;

class Rename extends AbstractController
{
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
        $id = $this->param('id');
        $newName = $this->param('name');

        $file = $this->fileRepository->find($id);

        if ($file->isset() && !$this->checkFileAccess($file)) {
            return $this->outputJSON([
                'error' => Translation::getInstance()->translate('ACCESS_DENIED')
            ]);
        }

        if ($this->fileRepository->renameFile($id, $newName)) {
            return $this->outputJSON([
                'status' => 'ok'
            ]);
        }
        else {
            return $this->outputJSON([
                'error' => Translation::getInstance()->translate('RENAME_FAILED')
            ]);
        }
    }

    public function actionChangemime()
    {
        $id = $this->param('id');
        $newMime = $this->param('mime');

        $file = $this->fileRepository->find($id);

        if ($file->isset() && $this->checkFileAccess($file)) {
            $file->setMime($newMime);
            return $this->outputJSON([
                'status' => 'ok'
            ]);
        }
        else {
            return $this->outputJSON([
                'error' => Translation::getInstance()->translate('ACCESS_DENIED')
            ]);
        }
    }
}