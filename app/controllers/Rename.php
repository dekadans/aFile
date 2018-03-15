<?php
namespace controllers;


use lib\Acl;
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

        if ($file->isset() && !Acl::checkFileAccess($file)) {
            $this->outputJSON([
                'error' => Registry::$language->translate('ACCESS_DENIED')
            ]);
        }

        if ($file->getName() === $newName || $file->rename($newName)) {
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