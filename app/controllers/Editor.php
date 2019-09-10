<?php
namespace controllers;

use lib\DataTypes\File;
use lib\DataTypes\FileContent;

class Editor extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function actionGet()
    {
        $fileString = $this->param('file');
        /** @var File $file */
        $file = $this->getFileRepository()->findByUniqueString($fileString);
        $fileEditable = $file->isEditable();

        if ($file->isFile() && $fileEditable !== false) {
            return $this->outputJSON([
                'id' => $file->getId(),
                'name' => $file->getName(),
                'date' => $file->getReadableDate($this->translation()),
                'code' => $fileEditable->isCode(),
                'markdown' => $fileEditable->isMarkdown(),
                'text' => $fileEditable->getText(),
                'downloadLink' => $fileEditable->getForceDownloadLink()
            ]);
        } else {
            return $this->outputJSON([
                'error' => 'Invalid file'
            ]);
        }
    }

    public function actionWrite()
    {
        $fileId = $this->param('id');
        $content = $this->param('content');

        $fileRepository = $this->getFileRepository();

        if ($fileId) {
            $file = $fileRepository->find($fileId);

            if ($file->isset() && $file->isFile()) {
                if ($this->checkFileAccess($file)) {
                    $tempFile = tempnam(sys_get_temp_dir(), 'afile');
                    $tempFileWritten = file_put_contents($tempFile, $content);
                    $content = new FileContent($tempFile);

                    if ($tempFileWritten) {
                        $result = $fileRepository->writeFileContent($file, $content);
                        unset($content);

                        if ($result) {
                            return $this->outputJSON([
                                'status' => 'ok'
                            ]);
                        }
                    }

                    return $this->outputJSON([
                        'error' => 'EDITOR_WRITE_ERROR'
                    ]);
                }

                return $this->outputJSON([
                    'error' => $this->translation()->translate('ACCESS_DENIED')
                ]);
            }
        }

        return $this->outputJSON([
            'error' => $this->translation()->translate('NO_FILE')
        ]);
    }
}
