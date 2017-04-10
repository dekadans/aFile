<?php

namespace lib;

class FileList {

    protected $files;
    protected $user;
    protected $location;

    public function __construct(User $user, $location) {
        $this->files = [];
        $this->user = $user;
        $this->location = $location;
    }

    /**
     * Creates the file list
     * @return [type] [description]
     */
    public function run() {
        $sql = "SELECT
                    id, name, size, mime, type, last_edit, string_id
                FROM files
                WHERE location = ?
                AND user_id = ?
                ORDER BY (
                CASE
                    WHEN type = 'DIRECTORY' THEN 1
                    WHEN type = 'SPECIAL' THEN 2
                    ELSE 4
                END), name";

        $files = Registry::get('db')->getPDO()->prepare($sql);
        $files->execute([$this->location, $this->user->getId()]);
        $this->files = $files->fetchAll();
        $this->filter();
        return $this->files;
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
