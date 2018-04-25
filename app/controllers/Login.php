<?php

namespace controllers;

use lib\Authentication;
use lib\Config;
use lib\Database;
use lib\Repositories\UserRepository;
use lib\Translation;

class Login extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_OPEN;
    }

    public function index() {
        $userRepository = new UserRepository(Database::getInstance());
        $authentication = new Authentication($userRepository, Config::getInstance()->login->remember_me_activated);

        if (Authentication::isSignedIn()) {
            $username = $this->param('username');
            $password = $this->param('password');
            $remember = ($this->param('remember') === 'true');

            if ($username && $password) {
                $result = $authentication->authenticate($username, $password);

                if ($result) {
                    if ($remember) {
                        $authentication->rememberMe($password);
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
