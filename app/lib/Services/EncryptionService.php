<?php
namespace lib\Services;

use lib\DataTypes\File;

class EncryptionService
{
    private $key;

    function __construct(string $key = '')
    {
        if (!empty($key)) {
            $this->setKey($key);
        }
    }

    public function encryptFile(File $file, string $pathToPlainText) {
        if ($file->isset()) {
            try {
                \Defuse\Crypto\File::encryptFile($pathToPlainText, $file->getFilePath(), $this->key);
                return true;
            }
            catch (\Defuse\Crypto\Exception\IOException $ex) {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function decryptFile(File $file) {
        $tempFile = tempnam(sys_get_temp_dir(), 'afile');

        if ($file->isset()) {
            try {
                \Defuse\Crypto\File::decryptFile($file->getFilePath(), $tempFile, $this->key);
                return $tempFile;
            }
            catch (\Defuse\Crypto\Exception\IOException $ex) {
                return false;
            }
            catch (\Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException $ex) {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public function setKey($key) {
        $this->key = \Defuse\Crypto\Key::loadFromAsciiSafeString($key);
    }
}
