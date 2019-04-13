<?php

namespace lib;

use lib\Repositories\FileRepository;
use lib\Repositories\UserRepository;

class File extends AbstractFile {
    protected $tmpPath;

    const ENCRYPTION_PERSONAL = 'PERSONAL';
    const ENCRYPTION_TOKEN = 'TOKEN';

    /** @var Encryption */
    private $encryptionService;

    public function __construct(FileRepository $fileRepository, UserRepository $userRepository, $data = null, Encryption $encryptionService = null)
    {
        parent::__construct($fileRepository, $userRepository, $data);
        $this->encryptionService = $encryptionService;
    }

    public function getContent()
    {
        return $this->fileRepository->readFileContent($this);
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
            return FileToken::createFromArray($shareData, $this->fileRepository);
        }
        else {
            return FileToken::createFromArray(['id' => null, 'file_id' => $this->id], $this->fileRepository);
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

            if ($this->encryptionService->encryptFile($this, $this->getPlainTextPath())) {
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
        return in_array($this->getMime(), Config::getInstance()->files->inline_download) || in_array($this->getMime(), Config::getInstance()->files->editor);
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
