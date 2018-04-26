<?php

namespace controllers;


use lib\Acl;
use lib\Repositories\FileRepository;
use lib\Translation;

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

        $fileRepository = new FileRepository();

        if (is_array($fileIds) && isset($newLocation)) {
            foreach ($fileIds as $id) {
                $file = $fileRepository->find($id);
                if ($file->isset() && Acl::checkFileAccess($file)) {
                    $result[] = $file->move($newLocation);
                }
            }
        }

        if (!in_array(false, $result)) {
            return $this->outputJSON([
                'status' => 'ok'
            ]);
        }
        else {
            return $this->outputJSON([
                'error' => Translation::getInstance()->translate('PASTE_FAILED')
            ]);
        }
    }
}