<?php

namespace lib\DataTypes;

class FileContent
{
    /** @var string */
    private $path;

    public function __construct(string $path)
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new CouldNotReadFileException('The requested file could not be read and/or decrypted.');
        }
        $this->path = $path;
    }

    public function getAsText()
    {
        $text = file_get_contents($this->path);
        return $text;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function __destruct()
    {
        @unlink($this->path);
    }
}

class CouldNotReadFileException extends \Exception {}