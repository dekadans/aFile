<?php
namespace controllers;

use lib\Authentication;
use lib\Repositories\FileRepository;
use lib\Translation;

class Create extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function actionDirectory()
    {
        $user = Authentication::getUser();
        $name = $this->param('name');
        $location = $this->param('location');
        $fileRepository = new FileRepository();

        if (!empty($name)) {
            $result = $fileRepository->createDirectory($user, $name, $location);

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