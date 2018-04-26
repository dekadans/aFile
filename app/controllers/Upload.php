<?php

namespace controllers;

use lib\Acl;
use lib\Authentication;
use lib\Config;
use lib\Repositories\FileRepository;
use lib\Translation;

class Upload extends AbstractController {
    private $location;
    private $user;

    /** @var FileRepository */
    private $fileRepository;

    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function init()
    {
        $this->fileRepository = new FileRepository();
    }

    public function index()
    {
        $this->location = $this->param('location');
        $this->user = Authentication::getUser();

        $previousFileWithSameName = null;

        $maxsize = Config::getInstance()->files->maxsize;

        $results = [];

        foreach ($_FILES as $file) {
            if ($file['error'] || $file['size'] > $maxsize) {
                $results[] = false;
                continue;
            }

            $name = $this->getUniqueName($file['name']);
            $mime = $this->getMimeType($file['name'], $file['tmp_name']);

            if (strcmp($name, $file['name']) !== 0) {
                $previousFileWithSameName = $file['name'];
            }

            $file = $this->fileRepository->createFile($this->user, $name, $this->location, $mime, $file['tmp_name']);
            $results[] = $file;
        }

        if (in_array(false, $results)) {
            return $this->outputJSON([
                'error' => Translation::getInstance()->translate('UPLOAD_FAILED')
            ]);
        }
        else if ($previousFileWithSameName && count($results) === 1) {
            $fileToOverwrite = $this->fileRepository->findByLocationAndName($this->user, $this->location, $previousFileWithSameName);
            $newFile = array_pop($results);

            return $this->outputJSON([
                'status' => 'confirm',
                'oldId' => $fileToOverwrite->getId(),
                'newId' => $newFile->getId(),
                'name' => $previousFileWithSameName
            ]);
        }
        else {
            return $this->outputJSON([
                'status' => 'ok'
            ]);
        }
    }

    public function actionConfirmoverwrite()
    {
        $oldFile = $this->fileRepository->find($this->param('oldId'));
        $newFile = $this->fileRepository->find($this->param('newId'));

        if (Acl::checkFileAccess($oldFile) && Acl::checkFileAccess($newFile)) {
            try {
                $newContentPath = $newFile->read(true);
                $oldFile->write($newContentPath);

                @unlink($newContentPath);
                $newFile->delete();

                return $this->outputJSON([
                    'status' => 'ok'
                ]);
            } catch(\Throwable $error) {
                return $this->outputJSON([
                    'error' => Translation::getInstance()->translate('FAILED'),
                    'msg' => $error->getMessage()
                ]);
            }
        }
        else {
            return $this->outputJSON([
                'error' => Translation::getInstance()->translate('ACCESS_DENIED')
            ]);
        }
    }

    private function getUniqueName(string $name)
    {
        if (!$this->fileRepository->exists($this->user, $name, $this->location)) {
            return $name;
        }

        $nameParts = explode('.', $name);
        $extension = array_pop($nameParts);
        $fileName = implode('.', $nameParts);

        return $fileName . '-' . uniqid() . '.' . $extension;
    }

    private function getMimeType(string $filename, string $temporaryLocation)
    {
        $detectedMime = mime_content_type($temporaryLocation);
        $extension = explode('.', $filename);
        $extension = array_pop($extension);

        switch ($extension) {
            case 'docx':
                return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            case 'pptx':
                return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
            case 'xlsx':
                return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            case 'svg':
                return 'image/svg+xml';
            default:
                return $detectedMime;
        }
    }
}
