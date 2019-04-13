<?php

namespace lib\DataTypes;

class FileList implements \Iterator, \Countable {

    /** @var int */
    private $position = 0;

    /** @var AbstractFile[] */
    private $files;

    /** @var bool */
    private $isSearchResult;

    private $location = null;

    /** @var User */
    private $user;

    /**
     * FileList constructor.
     * @param AbstractFile[] $files
     * @param bool $isSearchResult
     */
    public function __construct(array $files, bool $isSearchResult = false)
    {
        $this->files = $files;
        $this->isSearchResult = $isSearchResult;

        if (count($files)) {
            $this->user = $files[0]->getUser();

            if (!$isSearchResult) {
                $this->location = $files[0]->getLocation();
            }
        }
    }


    /**
     * Iterator interface implementation
     */

     public function rewind()
     {
         $this->position = 0;
     }

     public function current()
     {
         return $this->files[$this->position];
     }

     public function key()
     {
         return $this->position;
     }

     public function next()
     {
         ++$this->position;
     }

     public function valid()
     {
         return isset($this->files[$this->position]);
     }

    public function count()
    {
        return count($this->files);
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

}
