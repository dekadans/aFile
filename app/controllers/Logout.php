<?php

namespace controllers;

class Logout extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        $this->authentication()->deauthenticate($this->getRequest());

        return $this->outputJSON([
            'status' => 'ok'
        ]);
    }
}
