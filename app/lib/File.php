<?php

namespace lib;

class File extends AbstractFile {
    protected $tmpPath;

    /**
     * FILE OPERATIONS
     */

    /**
    * Reads and returns the contents of the File
    * @return blob
    */
    public function read() {
        $encryptionKey = $this->getEncryptionKey();

        if ($encryptionKey) {
            $encryption = new Encryption($encryptionKey);

            // !!!!! OLD CODE !!!!

            $fh = fopen($this->getFilePath(), 'rb');
            $encContent = fread($fh, filesize($this->getFilePath()));
            fclose($fh);

            if ($encContent) {
                $content = $encryption->decrypt($encContent);
                return $content;
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
    public function write($pathToContent = null) {
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

    public function getEncryptionKey() {
        if ($this->encryption == 'PERSONAL') {
            if (Registry::get('user')) {
                return Registry::get('user')->getKey();
            }
        }
        else {
            $sharingData = $this->getSharingInfo();
            return $sharingData['encryption_key'] ?? false;
        }

        // Token keys to be implemented

        return false;
    }

    public function getSharingInfo() {
        $shareQuery = Registry::get('db')->getPDO()->prepare('SELECT * FROM share WHERE file_id = ?');
        $shareQuery->execute([$this->id]);
        $shareData = $shareQuery->fetch();

        return $shareData;
    }

    public function openFileInNewTab() {
        return in_array($this->getMime(), Registry::get('config')->files->inline_download);
    }

    /**
    *  STATIC
    */

    /**
     * Creates a new file in the database and returns File object for it
     * @param  user $user
     * @param  string $name
     * @param  string $location
     * @param  string $mime
     * @param  string $tmpPath
     * @return File | boolean
     */
    public static function create(User $user, $name, $location, $mime, $tmpPath) {
        if (!self::exists($user, $name, $location)) {
            $string_id = self::getUniqueStringId();

            $addFile = Registry::get('db')->getPDO()->prepare('INSERT INTO files (user_id, name, location, mime, type, string_id) VALUES (?,?,?,?,?,?)');

            try {
                if ($addFile->execute([$user->getId(), $name, $location, $mime, 'FILE', $string_id])) {
                    $file = new self(Registry::get('db')->getPDO()->lastInsertId());
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

    public function getFilePath() {
        return __DIR__ . '/' . Registry::get('config')->files->path . $this->id;
    }

    /**
     * Get the value of tmpPath
     *
     * @return mixed
     */
    public function getTmpPath()
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

    public function getFileExtension() {
        $fileNameParts = explode('.', $this->name);
        $extension = array_pop($fileNameParts);
        return $extension;
    }
}
