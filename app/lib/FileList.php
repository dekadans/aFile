<?php

namespace lib;

class FileList implements \ArrayAccess, \Countable {

    protected $files;
    protected $user;
    protected $location;

    public function __construct(User $user, $location) {
        $this->files = [];
        $this->user = $user;
        $this->location = $location;
        $this->run();
    }

    /**
     * Creates the file list
     * @return [type] [description]
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
     * Array interface implementation
     */

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->files[] = $value;
        } else {
            $this->files[$offset] = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->files[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->files[$offset]);
    }

    public function offsetGet($offset) {
        if (isset($this->files[$offset])) {
            return $this->files[$offset];
        }

        return null;
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
     * @param mixed location
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
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the value of User
     *
     * @param mixed user
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
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

}
