<?php

namespace controllers;

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
        $fileRepository = $this->getFileRepository();
        $sizeInDb = $fileRepository->findTotalSizeForUser($this->authentication()->getUser());

        return $this->outputJSON([
            'b' => $sizeInDb,
            'h' => $fileRepository->convertBytesToReadable($sizeInDb, 2)
        ]);
    }
}