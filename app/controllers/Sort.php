<?php
namespace controllers;


use lib\AbstractFile;
use lib\Singletons;

class Sort extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function index()
    {
        $column = $this->param('column');

        Singletons::$sort->setSortBy($column);

        self::outputJSON([
            'status' => 'ok'
        ]);
    }
}