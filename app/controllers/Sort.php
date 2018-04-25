<?php
namespace controllers;

class Sort extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function index()
    {
        $column = $this->param('column');

        \lib\Sort::getInstance()->setSortBy($column);

        self::outputJSON([
            'status' => 'ok'
        ]);
    }
}