<?php

namespace controllers;

class Paste extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function index()
    {
        $fileIds = explode(',', $this->param('id'));
        $newLocation = $this->param('location');
        $result = [];

        $fileRepository = $this->getFileRepository();

        if (is_array($fileIds)) {
            foreach ($fileIds as $id) {
                $file = $fileRepository->find($id);
                if ($file->isset() && $this->checkFileAccess($file)) {
                    $result[] = $fileRepository->updateFileLocation($id, $newLocation);
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
                'error' => $this->translation()->translate('PASTE_FAILED')
            ]);
        }
    }
}