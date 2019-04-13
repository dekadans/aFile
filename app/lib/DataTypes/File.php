<?php

namespace lib\DataTypes;

use lib\Config;

class File extends AbstractFile {

    public function getContent()
    {
        return $this->fileRepository->readFileContent($this);
    }

    public function openFileInNewTab()
    {
        return in_array($this->getMime(), Config::getInstance()->files->inline_download) || in_array($this->getMime(), Config::getInstance()->files->editor);
    }

    public function getFilePath() : string
    {
        return __DIR__ . '/../../../' . Config::getInstance()->files->path . $this->getUser()->getId() . '/' . $this->id;
    }

    public function getFileExtension() : string
    {
        $fileNameParts = explode('.', $this->name);
        $extension = array_pop($fileNameParts);
        return strtolower($extension);
    }
}
