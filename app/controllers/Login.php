<?php

namespace controllers;

class Login extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_OPEN;
    }

    public function index() {
        $translation = $this->translation();

        if (!$this->authentication()->isSignedIn()) {
            $username = $this->param('username');
            $password = $this->param('password');

            if ($username && $password) {
                $result = $this->authentication()->authenticate($username, $password);

                if ($result) {
                    return $this->outputJSON([
                        'status' => 'ok'
                    ]);
                } else {
                    $errorMessage = $translation->translate('LOGIN_FAILED');
                }
            } else {
                $errorMessage = $translation->translate('LOGIN_MISSING_PARAMETERS');
            }
        } else {
            $errorMessage = $translation->translate('ALREADY_SIGNED_IN');
        }

        return $this->outputJSON([
            'loginError' => $errorMessage
        ]);
    }

    public function actionForm() {
        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
            $isHttps = false;
        } else {
            $isHttps = true;
        }

        return $this->parseView('loginForm', [
            'isHttps' => $isHttps
        ]);
    }
}
