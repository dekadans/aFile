<?php

namespace app\controllers;

class Test extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        //$user = \app\lib\Registry::get('user');
        //\app\lib\File::createFile($user, 'Test6.txt', base64_encode('/'), 'text/plain', 'tjena dumsnut åäö');

        $list = new \app\lib\FileList(\app\lib\Registry::get('user'), 'Lw==');
        var_dump($list->run());
    }
}
