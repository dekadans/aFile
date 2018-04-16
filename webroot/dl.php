<?php
require_once '../app/init.php';

$fileIdString = filter_input(INPUT_SERVER, 'PATH_INFO');

if ($fileIdString) {
    $fileIdString = substr($fileIdString, 1) . '/';
    list($id, $token) = explode('/', $fileIdString);

    if (strlen($id) == \lib\Registry::get('config')->files->id_string_length) {
        $dl = new \lib\Download($id);
        // AYAA == Ayaa ???
        if ($dl->getFile() && $dl->getFile()->isset()) {
            if (\lib\Acl::checkDownloadAccess($dl, $token)) {
                $dl->download();
            }
            else {
                die('Access denied');
            }
        }
    }
}


die('Invalid file.');
