<?php

namespace lib;

use lib\Repositories\FileRepository;

class File extends AbstractFile {
    protected $tmpPath;

    const ENCRYPTION_PERSONAL = 'PERSONAL';
    const ENCRYPTION_TOKEN = 'TOKEN';

    /**
     * FILE OPERATIONS
     */

    /**
    * Reads and returns the contents of the File
    * @return string|bool
    */
    public function read($returnPathToContent = false)
    {
        $encryptionKey = $this->getEncryptionKey();

        if ($encryptionKey) {
            $encryption = new Encryption($encryptionKey);

            $tempFile = $encryption->decryptFile($this);

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

        $encryption = new Encryption($encryptionKey);

        $result = $encryption->encryptFile($this);

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

    public function getEncryptionKey()
    {
        if ($this->encryption == self::ENCRYPTION_PERSONAL) {
            if (Singletons::$auth->isSignedIn()) {
                return Singletons::$auth->getUser()->getKey();
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
        $shareQuery = Singletons::$db->getPDO()->prepare('SELECT * FROM share WHERE file_id = ?');
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
        $encryption = new Encryption($this->getEncryptionKey());
        $unencryptedFile = $encryption->decryptFile($this);

        if ($unencryptedFile) {
            $encryption->setKey($encryptionKey);

            if ($encryption->encryptFile($this)) {
                @unlink($this->getTmpPath());
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
        return in_array($this->getMime(), Singletons::get('config')->files->inline_download);
    }

    public function delete() : bool
    {
        @unlink($this->getFilePath());
        return $this->deleteFileFromDatabase();
    }

    /**
    *  STATIC
    */

    /**
     * Creates a new file in the database and returns File object for it
     * @param  User $user
     * @param  string $name
     * @param  string $location
     * @param  string $mime
     * @param  string $tmpPath
     * @return AbstractFile | boolean
     */
    public static function create(User $user, $name, $location, $mime, $tmpPath)
    {
        if (!FileRepository::exists($user, $name, $location)) {
            $string_id = self::getUniqueStringId();

            $addFile = Singletons::$db->getPDO()->prepare('INSERT INTO files (user_id, name, location, mime, type, string_id) VALUES (?,?,?,?,?,?)');

            try {
                if ($addFile->execute([$user->getId(), $name, $location, $mime, 'FILE', $string_id])) {
                    $file = FileRepository::find(Singletons::$db->getPDO()->lastInsertId());
                    $file->setTmpPath($tmpPath);
                    $file->write();
                    return $file;
                }
                else {
                    return false;
                }
            }
            catch (\PDOException $e) {
                return false;
            }
        }
        else {
            return false;
        }
    }


    /**
    * GETTERS AND SETTERS
    */

    public function getFilePath() : string
    {
        return __DIR__ . '/' . Singletons::get('config')->files->path . $this->id;
    }

    /**
     * Get the value of tmpPath
     *
     * @return string
     */
    public function getTmpPath() : string
    {
        return $this->tmpPath;
    }

    /**
     * Set the value of tmpPath
     */
    public function setTmpPath($path)
    {
        $this->tmpPath = $path;
    }

    public function getFileExtension() : string
    {
        $fileNameParts = explode('.', $this->name);
        $extension = array_pop($fileNameParts);
        return $extension;
    }
}
