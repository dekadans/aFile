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
        $decodedPath = base64_decode($this->location);

        if ($decodedPath !== '/') {
            $decodedPath .= '/';
        }

        $decodedPath .= $this->name;
        $encodedPath = base64_encode($decodedPath);

        $fileList = new FileList($this->user, $encodedPath);

        if (count($fileList) === 0) {
            return $this->deleteFileFromDatabase();
        }
        else {
            return false;
        }
    }
}