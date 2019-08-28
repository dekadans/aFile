<?php

namespace controllers;

use lib\DataTypes\File;
use lib\DataTypes\FileContent;
use lib\Repositories\FileRepository;
use lib\Services\CreateFileService;
use lib\Translation;
use Psr\Http\Message\UploadedFileInterface;

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
        $this->fileRepository = $this->getFileRepository();
    }

    public function index()
    {
        $this->location = $this->param('location');
        $this->user = $this->authentication()->getUser();

        $createFileService = new CreateFileService($this->fileRepository);

        $previousFileWithSameName = null;

        $maxsize = $this->config()->find('files', 'maxsize');

        $results = [];

        /** @var UploadedFileInterface $file */
        foreach ($this->getRequest()->getUploadedFiles() as $file) {
            if ($file->getError() || $file->getSize() > $maxsize) {
                $results[] = false;
                continue;
            }

            $name = $this->getUniqueName($file->getClientFilename());
            $mime = $this->getMimeType($file->getClientMediaType(), $file->getClientFilename());

            if (strcmp($name, $file->getClientFilename()) !== 0) {
                $previousFileWithSameName = $file->getClientFilename();
            }

            $temporaryFile = tempnam(sys_get_temp_dir(), 'afile_upload');
            $file->moveTo($temporaryFile);
            $fileContent = new FileContent($temporaryFile);

            $result = $createFileService->createFile($this->user, $name, $this->location, $mime, $fileContent);
            $results[] = $result;
        }

        if (in_array(false, $results)) {
            return $this->outputJSON([
                'error' => Translation::getInstance()->translate('UPLOAD_FAILED')
            ]);
        } else if ($previousFileWithSameName && count($results) === 1) {
            $fileToOverwrite = $this->fileRepository->findByLocationAndName($this->user, $this->location, $previousFileWithSameName);
            $newFile = array_pop($results);

            return $this->outputJSON([
                'status' => 'confirm',
                'oldId' => $fileToOverwrite->getId(),
                'newId' => $newFile->getId(),
                'name' => $previousFileWithSameName
            ]);
        } else {
            return $this->outputJSON([
                'status' => 'ok'
            ]);
        }
    }

    public function actionConfirmoverwrite()
    {
        /** @var File $oldFile */
        $oldFile = $this->fileRepository->find($this->param('oldId'));
        /** @var File $newFile */
        $newFile = $this->fileRepository->find($this->param('newId'));

        if ($this->checkFileAccess($oldFile) && $this->checkFileAccess($newFile)) {
            try {
                $newContent = $newFile->getContent();

                $this->fileRepository->writeFileContent($oldFile, $newContent);

                unset($newContent);
                $this->fileRepository->deleteFile($newFile->getId());

                return $this->outputJSON([
                    'status' => 'ok'
                ]);
            } catch(\Throwable $error) {
                return $this->outputJSON([
                    'error' => Translation::getInstance()->translate('FAILED'),
                    'msg' => $error->getMessage()
                ]);
            }
        } else {
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

    private function getMimeType(string $mimeType, string $filename)
    {
        $extension = explode('.', $filename);
        $extension = array_pop($extension);

        if (in_array($extension, $this->config()->find('type_groups', 'code'))) {
            return 'text/plain';
        }

        switch ($extension) {
            case 'docx':
                return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            case 'pptx':
                return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
            case 'xlsx':
                return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            case 'svg':
                return 'image/svg+xml';
            case 'md':
                return 'text/plain';
            default:
                return $mimeType;
        }
    }
}
