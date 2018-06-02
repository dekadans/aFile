<?php

namespace lib;

class File extends AbstractFile {
    protected $tmpPath;

    const ENCRYPTION_PERSONAL = 'PERSONAL';
    const ENCRYPTION_TOKEN = 'TOKEN';

    /** @var Encryption */
    private $encryptionService;

    public function __construct($data = null, Encryption $encryptionService = null)
    {
        parent::__construct($data);
        $this->encryptionService = $encryptionService;
    }

    /**
     * FILE OPERATIONS
     */

    /**
     * Reads and returns the contents of the File
     * @param bool $returnPathToContent
     * @return string|bool
     */
    public function read($returnPathToContent = false)
    {
        $encryptionKey = $this->getEncryptionKey();

        if ($encryptionKey) {
            $this->encryptionService->setKey($encryptionKey);

            $tempFile = $this->encryptionService->decryptFile($this);

            if ($tempFile) {
                if ($returnPathToContent) {
                    return $tempFile;
                }
                else {
                    $content = file_get_contents($tempFile);
                    @unlink($tempFile);
                    return $content;
                }
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    /**
     * More or less an alias for read(true)
     * Decrypts and sets the temporary path
     * @return bool
     */
    public function decrypt()
    {
        return (bool) $this->read(true);
    }

    /**
     * Writes data to the file
     * @param  string $pathToContent
     * @return boolean
     */
    public function write($pathToContent = null) : bool
    {
        if (!is_null($pathToContent)) {
            $this->tmpPath = $pathToContent;
        }

        $encryptionKey = $this->getEncryptionKey();

        if (empty($encryptionKey)) {
            return false;
        }

        $this->encryptionService->setKey($encryptionKey);

        $result = $this->encryptionService->encryptFile($this);

        if ($result && is_file($this->getFilePath())) {
            $this->update([
                'size' => filesize($this->getFilePath()),
                'last_edit' => date('Y-m-d H:i:s')
            ]);
        }

        return (boolean) $result;
    }

    /*
    Encryption and sharing
     */

    private function getEncryptionKey()
    {
        if ($this->encryption == self::ENCRYPTION_PERSONAL) {
            if (Authentication::isSignedIn()) {
                return Authentication::getUser()->getKey();
            }
        }
        else {
            $token = $this->getToken();
            return $token->getEncryptionKey() ?? false;
        }

        return false;
    }

    /**
     * @return FileToken|null
     */
    public function getToken()
    {
        $shareQuery = Database::getInstance()->getPDO()->prepare('SELECT * FROM share WHERE file_id = ?');
        $shareQuery->execute([$this->id]);
        $shareData = $shareQuery->fetch();

        if ($shareData) {
            return FileToken::createFromArray($shareData);
        }
        else {
            return FileToken::createFromArray(['id' => null, 'file_id' => $this->id]);
        }
    }

    /**
     * @param string $encryptionKey Key in ASCII format
     * @param string $type
     * @return bool
     */
    public function changeEncryptionKey($encryptionKey, $type)
    {
        $this->encryptionService->setKey($this->getEncryptionKey());
        $unencryptedFile = $this->encryptionService->decryptFile($this);

        if ($unencryptedFile) {
            $this->encryptionService->setKey($encryptionKey);

            if ($this->encryptionService->encryptFile($this)) {
                @unlink($this->getPlainTextPath());
                $this->update([
                    'encryption' => $type
                ]);
                return true;
            }
        }
        return false;
    }

    public function openFileInNewTab()
    {
        return in_array($this->getMime(), Config::getInstance()->files->inline_download);
    }

    public function delete() : bool
    {
        @unlink($this->getFilePath());
        return $this->deleteFileFromDatabase();
    }

    /**
    * GETTERS AND SETTERS
    */

    public function getFilePath() : string
    {
        return __DIR__ . '/../../' . Config::getInstance()->files->path . $this->getUser()->getId() . '/' . $this->id;
    }

    /**
     * Get the value of tmpPath
     *
     * @return string
     */
    public function getPlainTextPath() : string
    {
        return $this->tmpPath;
    }

    /**
     * Set the value of tmpPath
     * @param string $path
     */
    public function setPlainTextPath(string $path)
    {
        $this->tmpPath = $path;
    }

    public function getFileExtension() : string
    {
        $fileNameParts = explode('.', $this->name);
        $extension = array_pop($fileNameParts);
        return strtolower($extension);
    }
}
