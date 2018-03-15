<?php

namespace controllers;


use lib\Acl;
use lib\FileRepository;
use lib\Registry;

class Paste extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function index()
    {
        $fileIds = $this->param('id');
        $newLocation = $this->param('location');
        $result = [];

        if (is_array($fileIds) && isset($newLocation)) {
            foreach ($fileIds as $id) {
                $file = FileRepository::find($id);
                if ($file->isset() && Acl::checkFileAccess($file)) {
                    $result[] = $file->move($newLocation);
                }
            }
        }

        if (!in_array(false, $result)) {
            $this->outputJSON([
                'status' => 'ok'
            ]);
        }
        else {
            $this->outputJSON([
                'error' => Registry::$language->translate('PASTE_FAILED')
            ]);
        }
    }
}