<?php

namespace lib;

class FileList implements \Iterator, \Countable {

    private $files;
    private $user;
    private $location;
    private $position = 0;

    public function __construct(User $user, $location) {
        $this->files = [];
        $this->user = $user;
        $this->location = $location;
        $this->run();
    }

    /**
     * Creates the file list
     */
    private function run() {
        $sql = "SELECT
                    *
                FROM files
                WHERE location = ?
                AND user_id = ?
                ORDER BY (
                CASE
                    WHEN type = 'DIRECTORY' THEN 1
                    WHEN type = 'SPECIAL' THEN 2
                    ELSE 4
                END), name";

        $filesQuery = Registry::get('db')->getPDO()->prepare($sql);
        $filesQuery->execute([$this->location, $this->user->getId()]);
        $filesResult = $filesQuery->fetchAll();

        foreach ($filesResult as $file) {
            if ($file['type'] === 'FILE') {
                $fileObject = new File();
            }
            else if ($file['type'] === 'DIRECTORY') {
                $fileObject = new Directory();
            }

            $fileObject->setByDatabaseRow($file);
            $this->files[] = $fileObject;
        }
    }

    /**
     * PRIVATE FUNCTIONS
     */

    private function filter() {
        for ($i = 0; $i < count($this->files); $i++) {
            $this->files[$i]['open_in_new_window'] = in_array($this->files[$i]['mime'], Registry::get('config')->files->inline_download);
        }
    }

    /**
     * Iterator interface implementation
     */

     public function rewind() {
         $this->position = 0;
     }

     public function current() {
         return $this->files[$this->position];
     }

     public function key() {
         return $this->position;
     }

     public function next() {
         ++$this->position;
     }

     public function valid() {
         return isset($this->files[$this->position]);
     }

    public function count() {
        return count($this->files);
    }

    /**
     * GETTERS AND SETTERS
     */

    /**
     * Set the value of Location
     *
     * @param string location
     *
     * @return self
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the value of Location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the value of User
     *
     * @param User user
     *
     * @return self
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of User
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

}
