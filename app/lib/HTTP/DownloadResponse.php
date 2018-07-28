<?php
namespace lib\HTTP;

use lib\Config;
use lib\File;

class DownloadResponse extends Response
{
    /** @var File */
    private $file;

    /** @var string */
    private $disposition = 'attachment';

    public function __construct(File $file, bool $inline = false)
    {
        parent::__construct('');
        $this->disposition = $inline ? 'inline' : 'attachment';
        $this->setFile($file);
    }

    public function setFile(File $file)
    {
        $this->file = $file;
        $this->addHeader('Content-Type', $this->file->getMime());
        $this->addHeader('Content-Disposition', $this->disposition . '; filename="'. $this->file->getName() .'"');
        $this->disableCache();

        $this->file->decrypt();
        $fileResource = fopen($this->file->getPlainTextPath(), 'r');
        $this->body = $fileResource;
    }

    /**
     * @param string $disposition
     */
    public function setDisposition(string $disposition)
    {
        $this->disposition = $disposition;
    }
}