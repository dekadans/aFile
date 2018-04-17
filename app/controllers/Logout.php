<?php

namespace controllers;

use lib\Singletons;

class Logout extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        Singletons::$auth->logout();

        $this->outputJSON([
            'status' => 'ok'
        ]);
    }
}
