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
        $translation = Translation::getInstance();
        $userRepository = new UserRepository(Database::getInstance());
        $authentication = new Authentication($userRepository, Config::getInstance()->login->remember_me_activated);

        if (!Authentication::isSignedIn()) {
            $username = $this->param('username');
            $password = $this->param('password');
            $remember = ($this->param('remember') === 'true');

            if ($username && $password) {
                $result = $authentication->authenticate($username, $password);

                if ($result) {
                    if ($remember) {
                        $authentication->rememberMe($password);
                    }

                    return $this->outputJSON([
                        'status' => 'ok'
                    ]);
                }
                else {
                    $errorMessage = $translation->translate('LOGIN_FAILED');
                }
            }
            else {
                $errorMessage = $translation->translate('LOGIN_MISSING_PARAMETERS');
            }
        }
        else {
            $errorMessage = $translation->translate('ALREADY_SIGNED_IN');
        }

        return $this->outputJSON([
            'loginError' => $errorMessage
        ]);
    }

    public function actionForm() {
        return $this->parseView('loginForm');
    }
}
