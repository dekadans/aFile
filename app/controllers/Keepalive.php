<?php

namespace controllers;

class Keepalive extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_OPEN;
    }

    public function index() {
        $signedIn = $this->authentication()->isSignedIn();

        if ($signedIn) {
            return $this->outputJSON(['status' => 'ok']);
        } else {
            return $this->outputJSON(['status' => 'nope']);
        }
    }
}
