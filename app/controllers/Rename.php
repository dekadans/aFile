<?php
namespace controllers;

use lib\Repositories\FileRepository;

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
                'error' => $this->translation()->translate('ACCESS_DENIED')
            ]);
        }

        if ($this->fileRepository->exists($this->authentication()->getUser(), $newName, $file->getLocation())) {
            return $this->outputJSON([
                'error' => $this->translation()->translate('FILE_EXISTS')
            ]);
        }

        if ($this->fileRepository->renameFile($id, $newName)) {
            return $this->outputJSON([
                'status' => 'ok'
            ]);
        }
        else {
            return $this->outputJSON([
                'error' => $this->translation()->translate('RENAME_FAILED')
            ]);
        }
    }

    public function actionChangemime()
    {
        $id = $this->param('id');
        $newMime = $this->param('mime');

        $file = $this->fileRepository->find($id);

        if ($file->isset() && $file->isFile() && $this->checkFileAccess($file)) {
            $this->fileRepository->updateFileMimeType($file->getId(), $newMime);

            return $this->outputJSON([
                'status' => 'ok'
            ]);
        }
        else {
            return $this->outputJSON([
                'error' => $this->translation()->translate('ACCESS_DENIED')
            ]);
        }
    }
}