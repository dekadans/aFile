<?php

namespace controllers;

use lib\Authentication;
use lib\Repositories\FileRepository;

class Info extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function actionHelp()
    {
        return $this->parseView('partials/help');
    }

    public function actionSize()
    {
        $fileRepository = new FileRepository();
        $sizeInDb = $fileRepository->findTotalSizeForUser(Authentication::getUser());

        return $this->outputJSON([
            'b' => $sizeInDb,
            'h' => FileRepository::convertBytesToReadable($sizeInDb, 2)
        ]);
    }
}