<?php

namespace lib;

use lib\Repositories\FileRepository;

class Download {
    /**
     * @var File
     */
    protected $file;

    public function __construct($id) {
        $this->file = FileRepository::findByUniqueString($id);
    }

    public function download() {
        $encryptionKey = $this->file->getEncryptionKey();

        if ($this->file->isFile() && file_exists($this->file->getFilePath()) && $encryptionKey) {
            $encryption = new Encryption($encryptionKey);
            $tempFile = $encryption->decryptFile($this->file);

            $disposition = in_array($this->file->getMime(), Singletons::get('config')->files->inline_download) ? 'inline' : 'attachment';

            header('Content-Type:' . $this->file->getMime());
            header("Cache-Control: no-cache, must-revalidate");
            header('Content-Disposition: '. $disposition .'; filename="'. $this->file->getName() .'"');
            readfile($tempFile);
            unlink($tempFile);
            die;
        }
        else {
            die('Could not download file.');
        }

    }

    /**
     * Get the value of File
     *
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }
}
