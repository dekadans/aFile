<?php

namespace app\controllers;

class Login extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_OPEN;
    }

    public function index() {
        if (!\app\lib\Registry::get('user')) {
            $username = $this->param('username');
            $password = $this->param('password');

            if ($username && $password) {
                $user = \app\lib\User::authenticate($username, $password);

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
                        'error' => 'LOGIN_FAILED'
                    ]);
                }
            }
            else {
                $this->outputJSON([
                    'error' => 'LOGIN_MISSING_PARAMETERS'
                ]);
            }
        }
        else {
            $this->outputJSON([
                'error' => 'ALREADY_SIGNED_IN'
            ]);
        }
    }
}
