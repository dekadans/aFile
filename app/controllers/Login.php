<?php

namespace controllers;

use lib\Singletons;
use lib\Translation;

class Login extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_OPEN;
    }

    public function index() {
        if (!Singletons::$auth->isSignedIn()) {
            $username = $this->param('username');
            $password = $this->param('password');
            $remember = ($this->param('remember') === 'true');

            if ($username && $password) {
                $result = Singletons::$auth->authenticate($username, $password);

                if ($result) {
                    if ($remember) {
                        Singletons::$auth->rememberMe($password);
                    }

                    $this->outputJSON([
                        'status' => 'ok'
                    ]);
                }
                else {
                    $this->outputJSON([
                        'error' => Translation::getInstance()->translate('LOGIN_FAILED')
                    ]);
                }
            }
            else {
                $this->outputJSON([
                    'error' => Translation::getInstance()->translate('LOGIN_MISSING_PARAMETERS')
                ]);
            }
        }
        else {
            $this->outputJSON([
                'error' => Translation::getInstance()->translate('ALREADY_SIGNED_IN')
            ]);
        }
    }

    public function actionForm() {
        $this->parseView('loginForm');
    }
}
