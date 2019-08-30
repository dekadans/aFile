<?php

namespace controllers;

class Delete extends AbstractController {
    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        $id = $this->param('id');
        $result = [];

        if (is_numeric($id)) {
            $result[] = $this->deleteFile($id);
        } else {
            $id = explode(',', $id);
            foreach ($id as $fileId) {
                $result[] = $this->deleteFile($fileId);
            }
        }

        if (!in_array(false, $result)) {
            return $this->outputJSON([
                'status' => 'ok'
            ]);
        } else {
            return $this->outputJSON([
                'error' => $this->translation()->translate('DELETE_FAILED')
            ]);
        }
    }

    private function deleteFile($id)
    {
        $fileRepository = $this->getFileRepository();
        $file = $fileRepository->find($id);
        if ($file->isset() && $this->checkFileAccess($file)) {
            return $fileRepository->deleteFile($id);
        }
        return true;
    }
}
