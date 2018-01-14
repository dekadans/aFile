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

    function __construct($key)
    {
        $this->setKey($key);
    }

    public function encryptFile(File $file) {
        if ($file->isset()) {
            try {
                \Defuse\Crypto\File::encryptFile($file->getTmpPath(), $file->getFilePath(), $this->key);
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
        $file->setTmpPath($tempFile);

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

    /* OLD CODE BELOW */

    const CYPHER = MCRYPT_RIJNDAEL_256;
    const MODE   = MCRYPT_MODE_CBC;

    public function encryptOld($plaintext)
    {
        $plaintext = gzcompress($plaintext);

        $td = mcrypt_module_open(self::CYPHER, '', self::MODE, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $this->key, $iv);
        $crypttext = mcrypt_generic($td, $plaintext);
        mcrypt_generic_deinit($td);
        return $iv.$crypttext;
    }

    public function decryptOld($crypttext)
    {
        $plaintext = '';
        $td        = mcrypt_module_open(self::CYPHER, '', self::MODE, '');
        $ivsize    = mcrypt_enc_get_iv_size($td);
        $iv        = substr($crypttext, 0, $ivsize);
        $crypttext = substr($crypttext, $ivsize);
        if ($iv)
        {
            mcrypt_generic_init($td, $this->key, $iv);
            $plaintext = mdecrypt_generic($td, $crypttext);
        }

        $plaintext = gzuncompress($plaintext);

        return $plaintext;
    }
}
