<?php

namespace lib\DataTypes;

use lib\Repositories\ConfigurationRepository;

class EditableFile
{
    /** @var File */
    private $file;
    /** @var string */
    private $urlToken;
    /** @var ConfigurationRepository */
    private $config;

    public function __construct(File $file, ConfigurationRepository $config)
    {
        $this->file = $file;
        $this->config = $config;
    }

    public function isMarkdown()
    {
        return $this->file->getFileExtension() === 'md';
    }

    public function isCode()
    {
        return in_array($this->file->getFileExtension(), $this->config->find('type_groups', 'code'));
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
        $text = $this->file->getContent()->getAsText();
        if (mb_detect_encoding($text, 'UTF-8', true) === false) {
            $text = utf8_encode($text);
        }
        return $text;
    }

    public function setUrlToken(string $urlToken)
    {
        $this->urlToken = $urlToken;
    }

    public function getForceDownloadLink()
    {
        $url = AFILE_LOCATION . 'dl' . ($this->config->find('files', 'skip_dl_php_extension') ? '' : '.php') . '/' . $this->file->getStringId();

        if (!empty($this->urlToken)) {
            $url .= '/' . $this->urlToken;
        }

        $url .= '/?fdl=1';

        return $url;
    }
}