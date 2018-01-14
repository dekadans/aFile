<?php

namespace controllers;

use \lib\File;

class Delete extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        $id = $this->param('id');
        $user = \lib\Registry::get('user');
        $result = false;

        if (is_numeric($id)) {
            $file = new File($id);
            if ($file->getId() !== '0' && $file->getUser()->getId() === $user->getId()) {
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
                'error' => 'DELETE_FAILED'
            ]);
        }
    }
}
