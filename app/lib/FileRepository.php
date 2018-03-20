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
        $checkFile = Registry::get('db')->getPDO()->prepare('SELECT * FROM files WHERE user_id = ? AND name = ? AND location = ?');
        $checkFile->execute([$user->getId(), $name, $location]);
        return $checkFile->fetch() ? true : false;
    }

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

        $filesQuery = Registry::get('db')->getPDO()->prepare($sql);
        $filesQuery->execute([$location, $user->getId()]);
        $filesResult = $filesQuery->fetchAll();

        foreach ($filesResult as $file) {
            $files[] = self::createFileObject($file);
        }

        return new FileList($files);
    }

    /**
     * @param User $user
     * @param string $filename
     * @param string $type
     * @return FileList
     */
    public static function findBySearchParameters(User $user, $filename = '', $type = '')
    {
        $searchCriteria = [];
        $files = [];

        if (isset(self::$searchTypes[$type])) {
            $mime = self::$searchTypes[$type];
        }

        if (empty($filename) && !isset($mime)) {
            return new FileList([], true);
        }

        if (strlen($filename)) {
            $searchCriteria[] = ' name LIKE :name ';
        }

        if (isset($mime)) {
            $typeSearch = "mime in ('". implode("','", $mime['*']) ."') ";

            foreach ($mime as $extension => $mimetypes) {
                if ($extension === '*') continue;

                $typeSearch .= " or (name like '%.". $extension ."' and mime in ('". implode("','", $mimetypes) ."')) ";
            }

            $searchCriteria[] = " (" . $typeSearch . ") ";
        }

        $searchCriteria[] = ' user_id = :user ';
        $searchCriteria[] = ' type = \'FILE\' ';

        $searchCriteria = implode('AND', $searchCriteria);

        $sql = "SELECT * FROM files WHERE
                {$searchCriteria}
                ORDER BY (
                CASE
                    WHEN type = 'DIRECTORY' THEN 1
                    WHEN type = 'SPECIAL' THEN 2
                    ELSE 4
                END), name";

        /** @var \PDO $pdo */
        $pdo = Registry::get('db')->getPDO();
        $searchQuery = $pdo->prepare($sql);
        $searchQuery->bindValue(':user', $user->getId());

        if (strlen($filename)) {
            $filename = '%'. str_replace('%', '\%', $filename) .'%';
            $searchQuery->bindParam(':name', $filename);
        }

        $searchQuery->execute();
        $searchResult = $searchQuery->fetchAll();

        foreach ($searchResult as $file) {
            $files[] = self::createFileObject($file);
        }

        return new FileList($files, true);
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

    private static $searchTypes = [
        'image' => [
            '*' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/svg+xml',
                'image/tiff'
            ],
            'svg' => [
                'text/plain'
            ]
        ],
        'document' => [
            '*' => [
                'application/pdf',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword',
                'application/vnd.oasis.opendocument.text',
                'application/rtf'
            ],
            'docx' => [
                'application/octet-stream',
                'application/zip'
            ]
        ]
    ];
}