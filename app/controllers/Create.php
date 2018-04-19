<?php
namespace controllers;

use lib\Directory;
use lib\Repositories\FileRepository;
use lib\Singletons;

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
            'error' => Singletons::$language->translate('CREATE_DIRECTORY_FAILED')
        ]);
    }
}