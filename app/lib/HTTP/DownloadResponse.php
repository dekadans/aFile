<?php
namespace lib\HTTP;

use lib\Config;
use lib\File;

class DownloadResponse extends Response
{
    /** @var File */
    private $file;

    public function __construct(File $file)
    {
        parent::__construct('');
        $this->file = $file;
    }

    public function output()
    {
        $this->addHeader('Content-Type: ' . $this->file->getMime());
        $this->addHeader('Content-Disposition: ' . $this->getDisposition() . '; filename="'. $this->file->getName() .'"');
        $this->disableCache();
        parent::output();

        readfile($this->file->getTmpPath());
        @unlink($this->file->getTmpPath());
    }

    private function getDisposition() : string
    {
        return in_array($this->file->getMime(), Config::getInstance()->files->inline_download) ? 'inline' : 'attachment';
    }
}