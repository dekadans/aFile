<?php

namespace controllers;

class Test extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        //$user = \lib\Registry::get('user');
        //\lib\File::createFile($user, 'Test6.txt', base64_encode('/'), 'text/plain', 'tjena dumsnut åäö');

        $list = new \lib\FileList(\lib\Registry::get('user'), 'Lw==');
        var_dump($list->run());
    }
}
