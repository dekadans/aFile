<?php
namespace controllers;

use lib\Directory;
use lib\Registry;

class Create extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function actionDirectory()
    {
        $user = Registry::get('user');
        $name = $this->param('name');
        $location = $this->param('location');

        if (!empty($name) && !empty($location)) {
            $result = Directory::create($user, $name, $location);

            if ($result) {
                $this->outputJSON([
                    'status' => 'ok'
                ]);
            }
        }

        $this->outputJSON([
            'error' => Registry::$language->translate('CREATE_DIRECTORY_FAILED')
        ]);
    }
}