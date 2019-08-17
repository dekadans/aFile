<?php

namespace lib\DataTypes;

use lib\Config;

class EditableFile
{
    /** @var File */
    private $file;
    /** @var string */
    private $urlToken;

    public function __construct(File $file)
    {
        $this->file = $file;
    }

    public function isMarkdown()
    {
        return $this->file->getFileExtension() === 'md';
    }

    public function isCode()
    {
        return in_array($this->file->getFileExtension(), Config::getInstance()->type_groups->code);
    }

    public function hasPreview()
    {
        return ($this->isMarkdown() || $this->isCode());
    }

    public function getFile() : File
    {
        return $this->file;
    }

    public function getText()
    {
        return $this->file->getContent()->getAsText();
    }

    public function setUrlToken(string $urlToken)
    {
        $this->urlToken = $urlToken;
    }

    public function getForceDownloadLink()
    {
        $url = AFILE_LOCATION . 'dl' . (Config::getInstance()->files->skip_dl_php_extension ? '' : '.php') . '/' . $this->file->getStringId();

        if (!empty($this->urlToken)) {
            $url .= '/' . $this->urlToken;
        }

        $url .= '/?fdl=1';

        return $url;
    }
}