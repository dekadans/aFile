<?php
require_once '../app/init.php';

$fileIdString = filter_input(INPUT_SERVER, 'PATH_INFO');

if ($fileIdString) {
    $fileIdString = substr($fileIdString, 1) . '/';
    list($id, $token) = explode('/', $fileIdString);

    if (!empty($id)) {
        $downloader = new \lib\Download($id, $token);
        $response = $downloader->download();
        echo $response->output();
        die;
    }
}

die('Error.');
