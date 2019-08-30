<?php
namespace controllers;

use lib\Services\SortService;

class Sort extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function index()
    {
        $column = $this->param('column');

        /** @var SortService $sort */
        $sort = $this->getContainer()->get(SortService::class);
        $sort->setSortBy($column);

        return $this->outputJSON([
            'status' => 'ok'
        ]);
    }
}