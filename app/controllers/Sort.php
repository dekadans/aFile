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

        $sort = $this->getContainer()->get(\lib\Services\SortService::class);
        $sort->setSortBy($column);

        return $this->outputJSON([
            'status' => 'ok'
        ]);
    }
}