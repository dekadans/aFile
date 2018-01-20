<?php

namespace lib;

abstract class AbstractFile {
    protected $id;
    protected $user;
    protected $user_id;
    protected $name;
    protected $location;
    protected $size;
    protected $mime;
    protected $type;
    protected $encryption;
    protected $last_edit;
    protected $created;
    protected $string_id;

    public function __construct()
    {
        $this->id = '0';
    }

    /**
     * Object setup
     */

    /*public function setById($id)
    {
        $checkFile = Registry::get('db')->getPDO()->prepare('SELECT * FROM files WHERE id = ?');
        $checkFile->execute([$id]);
        $fileData = $checkFile->fetch();
        $this->setByDatabaseRow($fileData);
    }

    public function setByUniqueString($string_id)
    {
        $checkFile = Registry::get('db')->getPDO()->prepare('SELECT * FROM files WHERE string_id = ?');
        $checkFile->execute([$string_id]);
        $fileData = $checkFile->fetch();
        $this->setByDatabaseRow($fileData);
    }*/

    public function setByDatabaseRow($fileData)
    {
        if ($fileData) {
            $this->id = $fileData['id'];
            $this->user_id = $fileData['user_id'];
            $this->name = $fileData['name'];
            $this->location = $fileData['location'];
            $this->size = $fileData['size'];
            $this->mime = $fileData['mime'];
            $this->type = $fileData['type'];
            $this->encryption = $fileData['encryption'];
            $this->last_edit = $fileData['last_edit'];
            $this->created = $fileData['created'];
            $this->string_id = $fileData['string_id'];
        }
        else {
            $this->id = '0';
        }
    }

    public function isset() : bool
    {
        return $this->id == '0' ? false : true;
    }

    abstract public function delete() : bool;

    /**
    * Deletes the File
    * @return boolean
    */
    protected function deleteFileFromDatabase() : bool
    {
        $deleteFile = Registry::get('db')->getPDO()->prepare('DELETE FROM files WHERE id = ?');

        if ($deleteFile->execute([$this->id])) {
            $this->id = null;
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Renames the file, if another file with that user/name/location combo doesn't exist
     * @param  string $newName
     * @return boolean
     */
    public function rename($newName) : bool
    {
        if (!$this->exists($this->user, $newName, $this->location)) {
            return $this->update(['name' => $newName]);
        }
        else {
            return false;
        }
    }


    /**
     * Updates columns in files database
     * @param  array $data
     * @return boolean
     */
    protected function update($data) : bool
    {
        $sets = [];
        foreach ($data as $column => $value) {
            if (!is_int($value)) {
                $value = "'" . $value . "'";
            }
            $sets[] = $column . '=' . $value;
        }
        $sets = implode(', ',$sets);

        $sql = 'UPDATE files SET ' . $sets . ' WHERE id = ?';
        $updateFile = Registry::get('db')->getPDO()->prepare($sql);
        try {
            return $updateFile->execute([$this->id]);
        }
        catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Checks if a file exists in the database
     * @param  user $user
     * @param  string $name
     * @param  string $location
     * @return boolean
     */
    public static function exists(User $user, $name, $location) : bool
    {
        $checkFile = Registry::get('db')->getPDO()->prepare('SELECT * FROM files WHERE user_id = ? AND name = ? AND location = ?');
        $checkFile->execute([$user->getId(), $name, $location]);
        return $checkFile->fetch() ? true : false;
    }

    /**
     * Generates a unique identifyer string for a files
     * @return string
     */
    protected static function getUniqueStringId() : string
    {
        $fileQuery = Registry::get('db')->getPDO()->prepare('SELECT id FROM files WHERE string_id = ?');
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $length = Registry::get('config')->files->id_string_length;

        while (true) {
            $randomString = '';

            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }

            $fileQuery->execute([$randomString]);

            if (!$fileQuery->fetch()) {
                return $randomString;
            }
        }
    }

    /**
     * Get the value of Id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of User
     *
     * @return User
     */
    public function getUser()
    {
        if (is_null($this->user)) {
            $this->user = new User($this->user_id);
        }

        return $this->user;
    }

    /**
     * Get the value of Name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of Location
     *
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getSizeReadable() : string
    {
        $siPrefix = Registry::get('config')->presentation->siprefix;
        $thresh = $siPrefix ? 1000 : 1024;
        $bytes = (int)$this->size;

        if ($bytes < $thresh) {
            return $this->size . ' B';
        }

        $units = $siPrefix ? ['kB','MB','GB','TB','PB','EB','ZB','YB'] : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];

        $u = -1;

        do {
            $bytes /= $thresh;
            $u++;
        } while ($bytes >= $thresh && $u < count($units) - 1);

        return round($bytes) . ' ' . $units[$u];
    }

    /**
     * Get the value of Mime
     *
     * @return mixed
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Get the value of Type
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the value of Encryption
     *
     * @return mixed
     */
    public function getEncryption()
    {
        return $this->encryption;
    }

    public function setEncryption($encryptionType)
    {
        $this->update(['encryption' => $encryptionType]);
    }

    /**
     * Get the value of Last Edit
     *
     * @return mixed
     */
    public function getLastEdit()
    {
        return $this->last_edit;
    }

    /**
     * Get the value of Created
     *
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    public function getStringId()
    {
        return $this->string_id;
    }

    public function isFile() : bool
    {
        return ($this->type === 'FILE');
    }

    public function isDirectory() : bool
    {
        return ($this->type === 'DIRECTORY');
    }
}
