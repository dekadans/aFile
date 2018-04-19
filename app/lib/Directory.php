<?php
/**
 * Created by PhpStorm.
 * User: Tomas
 * Date: 2018-01-16
 * Time: 19:26
 */

namespace lib;

class Directory extends AbstractFile
{
    public function delete() : bool
    {
        return $this->deleteFileFromDatabase();
    }

    public function read($returnPathToContent = false)
    {
        return '';
    }

    public function write($pathToContent = null) : bool
    {
        return true;
    }
}