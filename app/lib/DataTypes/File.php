<?php

namespace lib\DataTypes;


class File extends AbstractFile {

    public function getContent()
    {
        return $this->fileRepository->readFileContent($this);
    }

    public function isInlineDownload()
    {
        return in_array($this->getMime(), $this->config->get('files', 'inline_download'));
    }

    public function isEditable()
    {
        if ($this->isFile() && in_array($this->getMime(), $this->config->get('files', 'editor'))) {
            return new EditableFile($this, $this->config);
        } else {
            return false;
        }
    }

    public function getFilePath() : string
    {
        return __DIR__ . '/../../../' . $this->config->get('files', 'path') . $this->getUser()->getId() . '/' . $this->id;
    }

    public function getFileExtension() : string
    {
        $fileNameParts = explode('.', $this->name);
        $extension = array_pop($fileNameParts);
        return strtolower($extension);
    }
}
