<?php

namespace controllers;

use lib\Registry;
use lib\User;

class Login extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_OPEN;
    }

    public function index() {
        if (!Registry::get('user')) {
            $username = $this->param('username');
            $password = $this->param('password');

            if ($username && $password) {
                $user = User::authenticate($username, $password);

                if ($user) {
                    $_SESSION['aFile_User'] = $user->getId();
                    $_SESSION['aFile_User_Key'] = $user->getKey();
                    session_regenerate_id();

                    $this->outputJSON([
                        'status' => 'ok'
                    ]);
                }
                else {
                    $this->outputJSON([
                        'error' => Registry::$language->translate('LOGIN_FAILED')
                    ]);
                }
            }
            else {
                $this->outputJSON([
                    'error' => Registry::$language->translate('LOGIN_MISSING_PARAMETERS')
                ]);
            }
        }
        else {
            $this->outputJSON([
                'error' => Registry::$language->translate('ALREADY_SIGNED_IN')
            ]);
        }
    }

    public function actionForm() {
        $this->parseView('loginForm');
    }
}
