<?php

namespace controllers;

use \lib\File;
use lib\Registry;
use lib\User;

class Delete extends AbstractController {
    /**
     * @var User
     */
    private $user;

    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        $id = $this->param('id');
        $this->user = Registry::get('user');
        $result = false;

        if (is_numeric($id)) {
            $file = new File($id);
            if ($file->getId() !== '0' && $file->getUser()->getId() === $this->user->getId()) {
                $result = $file->delete();
            }
        }

        if ($result) {
            $this->outputJSON([
                'status' => 'ok'
            ]);
        }
        else {
            $this->outputJSON([
                'error' => Registry::$language->translate('DELETE_FAILED')
            ]);
        }
    }
}
