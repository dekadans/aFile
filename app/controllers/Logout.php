<?php

namespace controllers;

class Logout extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        $everywhere = $this->param('everywhere') === 'true' ? true : false;

        $this->authentication()->deauthenticate($this->getRequest(), $everywhere);

        return $this->outputJSON([
            'status' => 'ok'
        ]);
    }
}
