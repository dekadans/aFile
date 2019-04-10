<?php
namespace controllers;


use lib\Acl;
use lib\Authentication;
use lib\File;
use lib\Repositories\FileRepository;
use lib\Translation;

class Editor extends AbstractController
{
    /** @var string */
    private $filename;

    /** @var string */
    private $content;

    /** @var string */
    private $location;

    /** @var File */
    private $file;

    /** @var FileRepository */
    private $fileRepository;

    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function init()
    {
        $this->filename = $this->param('filename');
        $this->content = $this->param('content');
        $this->location = $this->param('location');

        $this->fileRepository = new FileRepository();

        $fileId = $this->param('id');
        if ($fileId) {
            $this->file = $this->fileRepository->find($fileId);

            if ($this->file->isset() && !Acl::checkFileAccess($this->file)) {
                return $this->outputJSON([
                    'error' => Translation::getInstance()->translate('ACCESS_DENIED')
                ]);
            }
        }
    }

    public function actionCreate()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'afile');
        file_put_contents($tempFile, $this->content);

        $file = $this->fileRepository->createFile(Authentication::getUser(), $this->filename, $this->location, 'text/plain', $tempFile);
        @unlink($tempFile);

        if ($file) {
            return $this->outputJSON([
                'status' => 'ok',
                'file_id' => $file->getId()
            ]);
        }
        else {
            return $this->outputJSON([
                'error' => 'EDITOR_CREATE_ERROR'
            ]);
        }
    }

    public function actionWrite()
    {
        if ($this->file->isset() && $this->file->isFile()) {
            $tempFile = tempnam(sys_get_temp_dir(), 'afile');
            $tempFileWritten = file_put_contents($tempFile, $this->content);

            if ($tempFileWritten !== false) {
                $fileWritten = $this->file->write($tempFile);
                @unlink($tempFile);

                if ($fileWritten) {
                    return $this->outputJSON([
                        'status' => 'ok'
                    ]);
                }
            }

            return $this->outputJSON([
                'error' => 'EDITOR_WRITE_ERROR'
            ]);
        }
        else {
            return $this->outputJSON([
                'error' => Translation::getInstance()->translate('NO_FILE')
            ]);
        }
    }
}