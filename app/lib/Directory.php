<?php
namespace lib;

class Directory extends AbstractFile
{
    public function read($returnPathToContent = false)
    {
        return '';
    }

    public function write($pathToContent = null) : bool
    {
        return true;
    }
}