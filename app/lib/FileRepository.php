<?php
namespace lib;


class FileRepository
{
    /**
     * @param integer $id
     * @return AbstractFile
     */
    public static function find($id)
    {
        $checkFile = Registry::get('db')->getPDO()->prepare('SELECT * FROM files WHERE id = ?');
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
        $checkFile = Registry::get('db')->getPDO()->prepare('SELECT * FROM files WHERE string_id = ?');
        $checkFile->execute([$stringId]);
        $fileData = $checkFile->fetch();

        return self::createFileObject($fileData);
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