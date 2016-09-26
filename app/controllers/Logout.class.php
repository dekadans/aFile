<?php

namespace app\controllers;

class Logout extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        unset($_SESSION['aFile_User']);
        unset($_SESSION['aFile_User_Key']);
        session_regenerate_id();
        
        $this->outputJSON([
            'status' => 'ok'
        ]);
    }
}
