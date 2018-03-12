<?php
namespace controllers;


use lib\File;
use lib\FileRepository;
use lib\Registry;

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

    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function init()
    {
        $this->filename = $this->param('filename');
        $this->content = $this->param('content');
        $this->location = $this->param('location');

        $fileId = $this->param('id');
        if ($fileId) {
            $this->file = FileRepository::find($fileId);
        }
    }

    public function actionCreate()
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'afile');
        file_put_contents($tempFile, $this->content);

        $file = File::create(Registry::get('user'), $this->filename, $this->location, 'text/plain', $tempFile);
        @unlink($tempFile);

        if ($file) {
            $this->outputJSON([
                'status' => 'ok',
                'file_id' => $file->getId()
            ]);
        }
        else {
            $this->outputJSON([
                'error' => 'EDITOR_CREATE_ERROR'
            ]);
        }
    }

    public function actionRead()
    {
        if ($this->file->isset() && $this->file->isFile()) {
            if (in_array($this->file->getMime(), Registry::get('config')->files->editor_enabled)) {
                $content = $this->file->read();

                if ($content) {
                    $this->outputJSON([
                        'filename' => $this->file->getName(),
                        'content' => $content
                    ]);
                }
                else {
                    $this->outputJSON([
                        'error' => 'EDITOR_READ_ERROR'
                    ]);
                }
            }
            else {
                $this->outputJSON([
                    'error' => Registry::$language->translate('EDITOR_TYPE_ERROR')
                ]);
            }
        }
        else {
            $this->outputJSON([
                'error' => Registry::$language->translate('NO_FILE')
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
                    if ($this->file->getName() !== $this->filename) {
                        $this->file->rename($this->filename);
                    }

                    $this->outputJSON([
                        'status' => 'ok'
                    ]);
                }
            }

            $this->outputJSON([
                'error' => 'EDITOR_WRITE_ERROR'
            ]);
        }
        else {
            $this->outputJSON([
                'error' => Registry::$language->translate('NO_FILE')
            ]);
        }
    }
}