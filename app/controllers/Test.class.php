<?php

namespace controllers;

class Test extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_OPEN;
    }

    public function index() {
        //$user = \lib\Registry::get('user');
        //\lib\File::createFile($user, 'Test6.txt', base64_encode('/'), 'text/plain', 'tjena dumsnut åäö');

        //$list = new \lib\FileList(\lib\Registry::get('user'), 'Lw==');
        //var_dump($list->run());

        /*$protected_key = \Defuse\Crypto\KeyProtectedByPassword::createRandomPasswordProtectedKey('aabbcc');
        $protected_key_encoded = $protected_key->saveToAsciiSafeString();
        var_dump($protected_key_encoded);
        var_dump($protected_key->unlockKey('aabbcc')->saveToAsciiSafeString());*/

        /*$file = new \lib\File('23');
        $enc = new \lib\Encryption(\lib\Registry::get('user')->getKey());
        $tempFile = $enc->decryptFile($file);
        $content = file_get_contents($tempFile);
        unlink($tempFile);
        echo($content);*/

        $str = strtr(base64_encode('tomas:Lw==:temp.txt'), '+=/', '-_~');
        echo $str;
    }
}
