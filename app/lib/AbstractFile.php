<?php

namespace lib;

use lib\Repositories\FileRepository;
use lib\Repositories\UserRepository;

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

    /** @var FileRepository */
    protected $fileRepository;

    public function __construct($data = null)
    {
        $this->fileRepository = new FileRepository();

        if ($data) {
            $this->setData($data);
        }
        else {
            $this->id = '0';
        }
    }

    protected function setData($fileData)
    {
        if ($fileData) {
            $this->id = $fileData['id'];
            $this->user_id = $fileData['user_id'];
            $this->name = $fileData['name'];
            $this->location = $fileData['parent_id'];
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
    abstract public function read($returnPathToContent = false);
    abstract public function write($pathToContent = null) : bool;

    /**
    * Deletes the File
    * @return boolean
    */
    protected function deleteFileFromDatabase() : bool
    {
        $deleteFile = Database::getInstance()->getPDO()->prepare('DELETE FROM files WHERE id = ?');

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
        if (!$this->fileRepository->exists($this->getUser(), $newName, $this->location)) {
            return $this->update(['name' => $newName]);
        }
        else {
            return false;
        }
    }

    /**
     * @param string $newMime
     * @return bool
     */
    public function setMime($newMime) : bool
    {
        if ($this->type === 'FILE') {
            $result = $this->update([
                'mime' => $newMime
            ]);
        }
        else {
            $result = false;
        }

        if ($result) {
            $this->mime = $newMime;
        }

        return $result;
    }

    /**
     * Moves a file to a new location
     * @param string $newLocation
     * @return bool
     */
    public function move($newLocation) : bool
    {
        if ($newLocation === $this->location) {
            return true;
        }

        if (!$this->fileRepository->exists($this->getUser(), $this->name, $newLocation)) {
            return $this->update(['parent_id' => $newLocation]);
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
        $updateFile = Database::getInstance()->getPDO()->prepare($sql);
        try {
            return $updateFile->execute([$this->id]);
        }
        catch (\PDOException $e) {
            return false;
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
            $repository = new UserRepository(Database::getInstance());
            $this->user = $repository->getUserById($this->user_id);
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
        $siPrefix = Config::getInstance()->presentation->siprefix;
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

    public function getReadableDateForFileList()
    {
        if (Config::getInstance()->presentation->upload_date_in_list) {
            $timestamp = strtotime($this->created);
        }
        else {
            $timestamp = strtotime($this->last_edit);
        }

        $now = time();

        if (date('Y-m-d', $timestamp) === date('Y-m-d', $now)) {
            $todayString = Translation::getInstance()->translate('TODAY');
            return $todayString . ' ' . date('H:i', $timestamp);
        }
        else if (date('Y', $timestamp) === date('Y', $now)) {
            return date('j M', $timestamp);
        }
        else {
            return date('j M Y', $timestamp);
        }
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
