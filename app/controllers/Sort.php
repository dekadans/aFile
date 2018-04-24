<?php
namespace controllers;

use lib\Sort as Sorting;

class Sort extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function index()
    {
        $column = $this->param('column');

        Sorting::getInstance()->setSortBy($column);

        self::outputJSON([
            'status' => 'ok'
        ]);
    }
}