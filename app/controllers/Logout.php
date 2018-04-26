<?php

namespace controllers;

use lib\Authentication;
use lib\Config;
use lib\Database;
use lib\Repositories\UserRepository;

class Logout extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        $userRepository = new UserRepository(Database::getInstance());
        $authentication = new Authentication($userRepository, Config::getInstance()->login->remember_me_activated);
        $authentication->logout();

        return $this->outputJSON([
            'status' => 'ok'
        ]);
    }
}
