<?php
namespace controllers;


use lib\Acl;
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
        $this->fileRepository = new FileRepository();
    }

    public function index()
    {
        $id = $this->param('id');
        $newName = $this->param('name');

        $file = $this->fileRepository->find($id);

        if ($file->isset() && !Acl::checkFileAccess($file)) {
            $this->outputJSON([
                'error' => Translation::getInstance()->translate('ACCESS_DENIED')
            ]);
        }

        if ($file->getName() === $newName || $file->rename($newName)) {
            $this->outputJSON([
                'status' => 'ok'
            ]);
        }
        else {
            $this->outputJSON([
                'error' => Translation::getInstance()->translate('RENAME_FAILED')
            ]);
        }
    }

    public function actionChangemime()
    {
        $id = $this->param('id');
        $newMime = $this->param('mime');

        $file = $this->fileRepository->find($id);

        if ($file->isset() && Acl::checkFileAccess($file)) {
            $file->setMime($newMime);
            $this->outputJSON([
                'status' => 'ok'
            ]);
        }
        else {
            $this->outputJSON([
                'error' => Translation::getInstance()->translate('ACCESS_DENIED')
            ]);
        }
    }
}