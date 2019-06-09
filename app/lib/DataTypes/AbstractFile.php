<?php

namespace lib\DataTypes;

use lib\Config;
use lib\Repositories\FileRepository;
use lib\Repositories\UserRepository;
use lib\Translation;

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
    /** @var UserRepository*/
    protected $userRepository;

    public function __construct(FileRepository $fileRepository, UserRepository $userRepository, $data = null)
    {
        $this->fileRepository = $fileRepository;
        $this->userRepository = $userRepository;

        if ($data) {
            $this->setData($data);
        }
        else {
            $this->id = '0';
        }
    }

    private function setData($fileData)
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

    /**
     * @param string $newMime
     * @return bool
     */
    public function setMime($newMime) : bool
    {
        if ($this->type === FileRepository::TYPE_FILE) {
            $result = $this->fileRepository->updateFileMimeType($this->id, $newMime);
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
            $this->user = $this->userRepository->getUserById($this->user_id);
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
        return FileRepository::convertBytesToReadable($this->size);
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
        return ($this->type === FileRepository::TYPE_FILE);
    }

    public function isDirectory() : bool
    {
        return ($this->type === FileRepository::TYPE_DIRECTORY);
    }
}
