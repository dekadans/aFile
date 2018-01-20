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

    /**
     * @param User $user
     * @param string $name
     * @param string $location
     * @return bool|AbstractFile
     */
    public static function create(User $user, $name, $location)
    {
        if (!self::exists($user, $name, $location)) {
            $string_id = self::getUniqueStringId();

            $addFile = Registry::get('db')->getPDO()->prepare('INSERT INTO files (user_id, name, location, type, encryption, string_id) VALUES (?,?,?,?,?,?)');

            try {
                if ($addFile->execute([$user->getId(), $name, $location, 'DIRECTORY', 'NONE', $string_id])) {
                    $directory = FileRepository::find(Registry::get('db')->getPDO()->lastInsertId());
                    return $directory;
                }
                else {
                    return false;
                }
            }
            catch (\PDOException $e) {
                return false;
            }
        }
        else {
            return false;
        }
    }
}