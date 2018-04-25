<?php
namespace lib\Repositories;

use lib\AbstractFile;
use lib\Config;
use lib\Database;
use lib\Directory;
use lib\File;
use lib\FileList;
use lib\SearchEngine;
use lib\Sort;
use lib\User;

class FileRepository
{
    /** @var \PDO */
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getPDO();
    }

    /**
     * @param User $user
     * @param string $name
     * @param int $location
     * @param string $mime
     * @param string $temporaryPath
     * @return File
     */
    public function createFile(User $user, string $name, $location, string $mime, string $temporaryPath)
    {
        /** @var File $file */
        $file = $this->create($user, $name, $location, $mime, 'FILE', 'PERSONAL');

        if ($file) {
            $file->setTmpPath($temporaryPath);
            $file->write();
        }

        return $file;
    }

    /**
     * @param User $user
     * @param string $name
     * @param int $location
     * @return bool|Directory
     */
    public function createDirectory(User $user, string $name, $location)
    {
        return $this->create($user, $name, $location, '', 'DIRECTORY', 'NONE');
    }

    private function create(User $user, $name, $location, $mime, $type, $encryption)
    {
        if (!$this->exists($user, $name, $location)) {
            $stringId = $this->getUniqueStringId();

            $SQL = "INSERT INTO files
                  (user_id, name, parent_id, mime, type, encryption, string_id)
                VALUES
                  (:user_id, :name, :location, :mime, :type, :encryption, :string_id)";

            $createStatement = $this->pdo->prepare($SQL);

            $createStatement->bindValue(':user_id', $user->getId());
            $createStatement->bindValue(':name', $name);
            $createStatement->bindValue(':location', $location, \PDO::PARAM_INT);
            $createStatement->bindValue(':mime', $mime);
            $createStatement->bindValue(':type', $type);
            $createStatement->bindValue(':encryption', $encryption);
            $createStatement->bindValue(':string_id', $stringId);

            if ($createStatement->execute()) {
                $file = $this->find($this->pdo->lastInsertId());
                return $file;
            }
            else {
                return false;
            }
        }
        else {
            return false;
        }
    }

    private function getUniqueStringId() : string
    {
        $fileQuery = $this->pdo->prepare('SELECT id FROM files WHERE string_id = ?');
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $length = Config::getInstance()->files->id_string_length;

        while (true) {
            $randomString = '';

            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, strlen($characters) - 1)];
            }

            $fileQuery->execute([$randomString]);

            if (!$fileQuery->fetch()) {
                return $randomString;
            }
        }
    }

    /**
     * Checks if a file exists in the database
     * @param  user $user
     * @param  string $name
     * @param  string $location
     * @return boolean
     */
    public function exists(User $user, $name, $location) : bool
    {
        $checkFile = $this->pdo->prepare('SELECT * FROM files WHERE user_id = ? AND name = ? AND parent_id '. (is_null($location) ? 'is' : '=') .' ?');
        $checkFile->execute([$user->getId(), $name, $location]);
        return $checkFile->fetch() ? true : false;
    }

    /**
     * @param integer $id
     * @return AbstractFile
     */
    public function find($id)
    {
        $checkFile = $this->pdo->prepare('SELECT * FROM files WHERE id = ?');
        $checkFile->execute([$id]);
        $fileData = $checkFile->fetch();

        return $this->createFileObject($fileData);
    }

    /**
     * @param string $stringId
     * @return AbstractFile
     */
    public function findByUniqueString($stringId)
    {
        $checkFile = $this->pdo->prepare('SELECT * FROM files WHERE string_id = ?');
        $checkFile->execute([$stringId]);
        $fileData = $checkFile->fetch();

        return $this->createFileObject($fileData);
    }

    /**
     * @param User $user
     * @param $location
     * @return FileList
     */
    public function findByLocation(User $user, $location)
    {
        $files = [];

        $sort = Sort::getInstance();

        $sql = "SELECT
                    *
                FROM files
                WHERE parent_id ". (is_null($location) ? 'is' : '=') ." ?
                AND user_id = ?
                ORDER BY (
                CASE
                    WHEN type = 'DIRECTORY' THEN 1
                    WHEN type = 'SPECIAL' THEN 2
                    ELSE 4
                END), " . $sort->getSortBy() . ' ' . $sort->getDirection();

        $filesQuery = $this->pdo->prepare($sql);
        $filesQuery->execute([($location ?? null), $user->getId()]);
        $filesResult = $filesQuery->fetchAll();

        foreach ($filesResult as $file) {
            $files[] = $this->createFileObject($file);
        }

        return new FileList($files);
    }

    /**
     * @param User $user
     * @param string $location
     * @param string $name
     * @return AbstractFile
     */
    public function findByLocationAndName(User $user, $location, string $name)
    {
        $fileQuery = $this->pdo->prepare('SELECT * FROM files WHERE user_id = ? AND name = ? AND parent_id = '. (is_null($location) ? 'is' : '=') .' ?');
        $fileQuery->execute([$user->getId(), $name, $location]);

        return $this->createFileObject($fileQuery->fetch());
    }

    /**
     * @param User $user
     * @param string $searchString
     * @return FileList
     */
    public function search(User $user, string $searchString = '')
    {
        $files = [];
        $engine = new SearchEngine($this->pdo, Sort::getInstance());
        $searchResult = $engine->search($searchString, $user->getId());

        foreach ($searchResult as $file) {
            $files[] = $this->createFileObject($file);
        }

        return new FileList($files, true);
    }

    /**
     * @param array $fileData
     * @return AbstractFile
     */
    private function createFileObject($fileData)
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