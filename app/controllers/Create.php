<?php
namespace controllers;

use lib\Directory;
use lib\Repositories\FileRepository;
use lib\Singletons;
use lib\Translation;

class Create extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function actionDirectory()
    {
        $user = Singletons::$auth->getUser();
        $name = $this->param('name');
        $location = $this->param('location');
        $fileRepository = new FileRepository();

        if (!empty($name)) {
            $result = $fileRepository->createDirectory($user, $name, $location);

            if ($result) {
                $this->outputJSON([
                    'status' => 'ok'
                ]);
            }
        }

        $this->outputJSON([
            'error' => Translation::getInstance()->translate('CREATE_DIRECTORY_FAILED')
        ]);
    }
}