<?php
namespace controllers;

use lib\Repositories\FileRepository;
use lib\Services\CreateFileService;
use lib\Translation;

class Create extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function actionDirectory()
    {
        $user = $this->authentication()->getUser();
        $name = $this->param('name');
        $location = $this->param('location');
        $fileRepository = $this->getFileRepository();
        $createFileService = new CreateFileService($fileRepository);

        if (!empty($name)) {
            $result = $createFileService->createDirectory($user, $name, $location);

            if ($result) {
                return $this->outputJSON([
                    'status' => 'ok'
                ]);
            }
        }

        return $this->outputJSON([
            'error' => Translation::getInstance()->translate('CREATE_DIRECTORY_FAILED')
        ]);
    }
}