<?php

namespace controllers;

use lib\DataTypes\FileContent;
use lib\Repositories\FileRepository;
use lib\Services\CreateFileService;
use lib\Translation;

class Link extends AbstractController
{
    public function getAccessLevel()
    {
        return self::ACCESS_LOGIN;
    }

    public function actionCreate()
    {
        $name = $this->param('name');
        $location = $this->param('location');
        $url = $this->param('url');

        if (!empty($name) && !empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            $fileRepository = new FileRepository();
            $createFileService = new CreateFileService($fileRepository);

            $fileContent = '[InternetShortcut]' . PHP_EOL . 'URL=' . $url;
            $tempFile = tempnam(sys_get_temp_dir(), 'afile');
            file_put_contents($tempFile, $fileContent);
            $fileContent = new FileContent($tempFile);

            $result = $createFileService->createLink($this->authentication()->getUser(), $name, $location, $fileContent);

            if ($result) {
                return $this->outputJSON(['status' => 'ok']);
            }
        }

        return $this->outputJSON(['status' => 'error', 'error' => Translation::getInstance()->translate('LINK_ERROR')]);
    }
}