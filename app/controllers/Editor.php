<?php
namespace controllers;

use lib\DataTypes\FileContent;
use lib\Translation;

class Editor extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
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
                    'error' => Translation::getInstance()->translate('ACCESS_DENIED')
                ]);
            }
        }

        return $this->outputJSON([
            'error' => Translation::getInstance()->translate('NO_FILE')
        ]);
    }
}