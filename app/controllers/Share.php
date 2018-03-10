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

    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function init() {
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

    public function index() {
    }

    public function actionCreate() {
        $password = $this->param('password');
        $validUntil = $this->param('valid');
        $sharing = new Sharing($this->file);

        if (isset($password)) {
            $result = $sharing->enablePassword($password, $validUntil);
        }
        else {
            $result = $sharing->enableOpen($validUntil);
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
}
