<?php
namespace lib;

/*
https://paragonie.com/white-paper/2015-secure-php-data-encryption
https://paragonie.com/blog/2015/05/if-you-re-typing-word-mcrypt-into-your-code-you-re-doing-it-wrong
https://github.com/defuse/php-encryption
*/
class Encryption
{
    private $key;

    function __construct(string $key = '')
    {
        if (!empty($key)) {
            $this->setKey($key);
        }
    }

    public function encryptFile(File $file) {
        if ($file->isset()) {
            try {
                \Defuse\Crypto\File::encryptFile($file->getPlainTextPath(), $file->getFilePath(), $this->key);
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
        $file->setPlainTextPath($tempFile);

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
