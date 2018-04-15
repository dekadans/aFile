<?php
namespace lib;


class FileRepository
{
    /**
     * Checks if a file exists in the database
     * @param  user $user
     * @param  string $name
     * @param  string $location
     * @return boolean
     */
    public static function exists(User $user, $name, $location) : bool
    {
        $checkFile = self::getPDO()->prepare('SELECT * FROM files WHERE user_id = ? AND name = ? AND location = ?');
        $checkFile->execute([$user->getId(), $name, $location]);
        return $checkFile->fetch() ? true : false;
    }

    /**
     * @param integer $id
     * @return AbstractFile
     */
    public static function find($id)
    {
        $checkFile = self::getPDO()->prepare('SELECT * FROM files WHERE id = ?');
        $checkFile->execute([$id]);
        $fileData = $checkFile->fetch();

        return self::createFileObject($fileData);
    }

    /**
     * @param string $stringId
     * @return AbstractFile
     */
    public static function findByUniqueString($stringId)
    {
        $checkFile = self::getPDO()->prepare('SELECT * FROM files WHERE string_id = ?');
        $checkFile->execute([$stringId]);
        $fileData = $checkFile->fetch();

        return self::createFileObject($fileData);
    }

    /**
     * @param User $user
     * @param $location
     * @return FileList
     */
    public static function findByLocation(User $user, $location)
    {
        $files = [];

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

        $filesQuery = self::getPDO()->prepare($sql);
        $filesQuery->execute([$location, $user->getId()]);
        $filesResult = $filesQuery->fetchAll();

        foreach ($filesResult as $file) {
            $files[] = self::createFileObject($file);
        }

        return new FileList($files);
    }

    /**
     * @param User $user
     * @param string $searchString
     * @return FileList
     */
    public static function search(User $user, string $searchString = '')
    {
        $files = [];
        $engine = new SearchEngine(self::getPDO());
        $searchResult = $engine->search($searchString, $user->getId());

        foreach ($searchResult as $file) {
            $files[] = self::createFileObject($file);
        }

        return new FileList($files, true);
    }

    /**
     * @return \PDO
     */
    private static function getPDO()
    {
        return Registry::get('db')->getPDO();
    }

    /**
     * @param array $fileData
     * @return AbstractFile
     */
    private static function createFileObject($fileData)
    {
        if ($fileData) {
            if ($fileData['type'] === 'FILE') {
                $file = new File($fileData);
            }
            else if ($fileData['type'] === 'DIRECTORY') {
                $file = new Directory($fileData);
            }
        }
        else {
            $file = new File();
        }

        return $file;
    }
}