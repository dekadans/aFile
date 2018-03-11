<?php

namespace controllers;

use lib\File;
use lib\FileRepository;
use lib\Registry;
use lib\Sharing;
use lib\User;

class Share extends AbstractController {
    /**
     * @var File
     */
    protected $file;

    /**
     * @var User
     */
    protected $user;

    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function init()
    {
        $fileId = $this->param('id');
        $this->file = FileRepository::find($fileId);
        $this->user = Registry::get('user');

        if (!$this->file->isset() && $this->file->isFile()) {
            $this->outputJSON([
                'error' => 'NO_FILE'
            ]);
        }

        if ($this->file->getUser()->getId() !== $this->user->getId()) {
            $this->outputJSON([
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
            $this->outputJSON([
                'status' => 'ok'
            ]);
        }
        else {
            $this->outputJSON([
                'error' => 'SHARING_ERROR'
            ]);
        }
    }

    public function actionDestroy()
    {
        $token = $this->file->getToken();
        if ($token->exists()) {
            if ($token->destroy()) {
                $this->outputJSON([
                    'status' => 'ok'
                ]);
            }
        }

        $this->outputJSON([
            'error' => 'COULD_NOT_DESTROY_TOKEN'
        ]);
    }

    public function actionPanel()
    {
        $token = $this->file->getToken();
        $this->parseView('partials/sharepanel', ['token' => $token, 'file' => $this->file]);
    }
}
