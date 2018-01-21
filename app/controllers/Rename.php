<?php
namespace controllers;


use lib\FileRepository;
use lib\Registry;

class Rename extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function index()
    {
        $id = $this->param('id');
        $newName = $this->param('name');

        $file = FileRepository::find($id);
        $result = $file->rename($newName);

        if ($result) {
            $this->outputJSON([
                'status' => 'ok'
            ]);
        }
        else {
            $this->outputJSON([
                'error' => Registry::$language->translate('RENAME_FAILED')
            ]);
        }
    }
}