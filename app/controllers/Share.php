<?php

namespace controllers;

use lib\Acl;
use lib\File;
use lib\Repositories\EncryptionKeyRepository;
use lib\Repositories\FileRepository;

class Share extends AbstractController {
    /**
     * @var File
     */
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

        if (!$this->file->isset() && $this->file->isFile()) {
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
        $token = $this->encryptionKeyRepository->findAccessTokenForFile($this->file);

        $result = $token->enableOpen();

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
        $token = $this->encryptionKeyRepository->findAccessTokenForFile($this->file);
        if ($token->exists()) {
            if ($token->destroy()) {
                return $this->outputJSON([
                    'status' => 'ok'
                ]);
            }
        }

        return $this->outputJSON([
            'error' => 'COULD_NOT_DESTROY_TOKEN'
        ]);
    }

    public function actionPanel()
    {
        $token = $this->encryptionKeyRepository->findAccessTokenForFile($this->file);
        return $this->parseView('partials/sharepanel', ['token' => $token, 'file' => $this->file]);
    }
}
