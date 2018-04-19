<?php

namespace controllers;

use lib\Acl;
use lib\Repositories\FileRepository;
use lib\Singletons;

class Delete extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        $id = $this->param('id');
        $result = [];

        if (is_numeric($id)) {
            $result[] = $this->deleteFile($id);
        }
        else if (is_array($id)) {
            foreach ($id as $fileId) {
                $result[] = $this->deleteFile($fileId);
            }
        }

        if (!in_array(false, $result)) {
            $this->outputJSON([
                'status' => 'ok'
            ]);
        }
        else {
            $this->outputJSON([
                'error' => Singletons::$language->translate('DELETE_FAILED')
            ]);
        }
    }

    private function deleteFile($id)
    {
        $fileRepository = new FileRepository();
        $file = $fileRepository->find($id);
        if ($file->isset() && Acl::checkFileAccess($file)) {
            return $file->delete();
        }
        return true;
    }
}
