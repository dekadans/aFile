<?php
namespace lib\Services;

use Defuse\Crypto\Core;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Encoding;
use Defuse\Crypto\Key;
use Defuse\Crypto\KeyProtectedByPassword;
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

    /**
     * Defuse doesn't provide a method for creating a KeyProtectedByPassword from a known Key, so this does it instead
     * @param Key $key
     * @param string $password
     */
    public function passwordEncryptKey(Key $key, string $password)
    {
        $passwordProtectedKey = Crypto::encryptWithPassword(
            $key->saveToAsciiSafeString(),
            \hash(Core::HASH_FUNCTION_NAME, $password, true),
            true
        );

        $protectedKeyAscii = Encoding::saveBytesToChecksummedAsciiSafeString(
            KeyProtectedByPassword::PASSWORD_KEY_CURRENT_VERSION,
            $passwordProtectedKey
        );

        return KeyProtectedByPassword::loadFromAsciiSafeString($protectedKeyAscii);
    }
}
