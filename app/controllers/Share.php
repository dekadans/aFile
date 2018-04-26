<?php

namespace controllers;

use lib\Acl;
use lib\File;
use lib\Repositories\FileRepository;

class Share extends AbstractController {
    /**
     * @var File
     */
    private $file;

    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function init()
    {
        $fileRepository = new FileRepository();
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
        $password = $this->param('password');
        $validUntil = $this->param('valid');
        $token = $this->file->getToken();

        if (isset($password)) {
            $result = $token->enablePassword($password, $validUntil);
        }
        else {
            $result = $token->enableOpen($validUntil);
        }

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
        $token = $this->file->getToken();
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
        $token = $this->file->getToken();
        return $this->parseView('partials/sharepanel', ['token' => $token, 'file' => $this->file]);
    }
}
