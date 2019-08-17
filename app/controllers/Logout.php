<?php

namespace controllers;

class Logout extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        $this->authenticationService->deauthenticate($this->request);

        return $this->outputJSON([
            'status' => 'ok'
        ]);
    }
}
