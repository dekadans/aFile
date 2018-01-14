<?php

namespace lib;

class Download {
    protected $file;

    public function __construct($id) {
        $this->file = new File();
        $this->file->setByUniqueString($id);
    }

    public function download() {
        $encryptionKey = $this->file->getEncryptionKey();

        if ($encryptionKey) {
            $encryption = new Encryption($encryptionKey);
            $tempFile = $encryption->decryptFile($this->file);

            $disposition = in_array($this->file->getMime(), Registry::get('config')->files->inline_download) ? 'inline' : 'attachment';

            header('Content-Type:' . $this->file->getMime());
            header("Cache-Control: no-cache, must-revalidate");
            header('Content-Disposition: '. $disposition .'; filename="'. $this->file->getName() .'"');
            readfile($tempFile);
            unlink($tempFile);
            die;
        }
    }

    /**
     * Get the value of File
     *
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }
}
