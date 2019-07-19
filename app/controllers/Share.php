<?php

namespace controllers;

use lib\Acl;
use lib\DataTypes\File;
use lib\Repositories\EncryptionKeyRepository;
use lib\Repositories\FileRepository;
use lib\Translation;

class Share extends AbstractController {
    /** @var File */
    private $file;

    /** @var EncryptionKeyRepository */
    private $encryptionKeyRepository;

    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function init()
    {
        $fileRepository = new FileRepository();
        $this->encryptionKeyRepository = new EncryptionKeyRepository($fileRepository);

        $fileId = $this->param('id');
        $this->file = $fileRepository->find($fileId);

        if (!$this->file->isset() || !$this->file->isDownloadable()) {
            return $this->outputJSON([
                'error' => 'NO_FILE'
            ]);
        }

        if (!Acl::checkFileAccess($this->file)) {
            return $this->outputJSON([
                'error' => 'NO_ACCESS'
            ]);
        }
    }

    public function index()
    {
    }

    public function actionCreate()
    {
        $result = $this->encryptionKeyRepository->createAccessTokenForFile($this->file);

        if ($result) {
            return $this->outputJSON([
                'status' => 'ok'
            ]);
        }
        else {
            return $this->outputJSON([
                'error' => 'SHARING_ERROR'
            ]);
        }
    }

    public function actionDestroy()
    {
        $result = $this->encryptionKeyRepository->removeAccessTokenForFile($this->file);

        if ($result) {
            return $this->outputJSON([
                'status' => 'ok'
            ]);
        } else {
            return $this->outputJSON([
                'error' => 'COULD_NOT_DESTROY_TOKEN'
            ]);
        }
    }

    public function actionActive()
    {
        $result = $this->encryptionKeyRepository->flipTokenActiveState($this->file);

        if ($result) {
            return $this->outputJSON([
                'status' => 'ok'
            ]);
        } else {
            return $this->outputJSON([
                'error' => Translation::getInstance()->translate('FAILED')
            ]);
        }
    }

    public function actionPassword()
    {
        $password = $this->param('password');

        if (is_null($password)) {
            $result = $this->encryptionKeyRepository->clearTokenPasswordForFile($this->file);
        } else {
            $result = $this->encryptionKeyRepository->setTokenPasswordForFile($this->file, $password);
        }

        if ($result) {
            return $this->outputJSON([
                'status' => 'ok'
            ]);
        } else {
            return $this->outputJSON([
                'error' => Translation::getInstance()->translate('FAILED')
            ]);
        }
    }

    public function actionPanel()
    {
        $token = $this->encryptionKeyRepository->findAccessTokenForFile($this->file);
        return $this->parseView('partials/sharepanel', ['token' => $token, 'file' => $this->file]);
    }
}
