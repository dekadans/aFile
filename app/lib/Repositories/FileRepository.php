<?php
namespace lib\Repositories;

use lib\DataTypes\AbstractFile;
use lib\Config;
use lib\Database;
use lib\DataTypes\Directory;
use lib\DataTypes\Link;
use lib\Services\EncryptionService;
use lib\DataTypes\File;
use lib\DataTypes\FileContent;
use lib\DataTypes\FileList;
use lib\Services\SortService;
use lib\DataTypes\User;

class FileRepository
{
    const TYPE_FILE = 'FILE';
    const TYPE_DIRECTORY = 'DIRECTORY';
    const TYPE_LINK = 'LINK';

    /** @var \PDO */
    private $pdo;

    /** @var UserRepository */
    private $userRepository;

    /** @var EncryptionService */
    private $encryption;

    /** @var EncryptionKeyRepository */
    private $encryptionKeyRepository;

    /** @var SortService */
    private $sort;

    public function __construct(Database $database,
                                UserRepository $userRepository,
                                EncryptionService $encryptionService,
                                EncryptionKeyRepository $encryptionKeyRepository,
                                SortService $sort)
    {
        $this->pdo = $database->getPDO();
        $this->userRepository = $userRepository;
        $this->encryption = $encryptionService;
        $this->encryptionKeyRepository = $encryptionKeyRepository;
        $this->sort = $sort;
    }

    /**
     * @param User $user
     * @param $name
     * @param $location
     * @param $mime
     * @param $type
     * @param $encryption
     * @return bool|AbstractFile
     */
    public function create(User $user, $name, $location, $mime, $type, $encryption)
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
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
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
     * @param int $fileId
     * @return bool
     */
    public function deleteFile(int $fileId)
    {
        $file = $this->find($fileId);

        $deleteFile = $this->pdo->prepare('DELETE FROM files WHERE id = ?');

        if ($deleteFile->execute([$file->getId()])) {
            if ($file instanceof File) {
                @unlink($file->getFilePath());
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param int $fileId
     * @param string $newName
     * @return bool
     */
    public function renameFile(int $fileId, string $newName) : bool
    {
        $file = $this->find($fileId);

        if (!$file->isset()) {
            return false;
        }

        if ($file->getName() === $newName) {
            return true;
        }

        if (!$this->exists($file->getUser(), $newName, $file->getLocation())) {
            return $this->updateFileProperties($file->getId(), ['name' => $newName]);
        } else {
            return false;
        }
    }

    /**
     * @param int $fileId
     * @param string $mimeType
     * @return bool
     */
    public function updateFileMimeType(int $fileId, string $mimeType) : bool
    {
        return $this->updateFileProperties($fileId, ['mime' => $mimeType]);
    }

    /**
     * @param int $fileId
     * @param string|null $newLocation
     * @return bool
     */
    public function updateFileLocation(int $fileId, string $newLocation = null)
    {
        $file = $this->find($fileId);

        if (!$file->isset()) {
            return false;
        }

        if ($file->getLocation() === $newLocation) {
            return true;
        }

        if (!$this->exists($file->getUser(), $file->getName(), $newLocation)) {
            return $this->updateFileProperties($file->getId(), ['parent_id' => $newLocation]);
        } else {
            return false;
        }
    }

    /**
     * @param int $fileId
     * @param array $data
     * @return bool
     */
    private function updateFileProperties(int $fileId, array $data) : bool
    {
        $sets = [];
        foreach ($data as $column => $value) {
            if (!is_int($value) && !is_null($value)) {
                $value = "'" . $value . "'";
            }
            else if (is_null($value)) {
                $value = 'NULL';
            }

            $sets[] = $column . '=' . $value;
        }
        $sets = implode(', ',$sets);

        $sql = 'UPDATE files SET ' . $sets . ' WHERE id = ?';
        $updateFile = $this->pdo->prepare($sql);
        try {
            return $updateFile->execute([$fileId]);
        }
        catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * @param File $file
     * @param string $fileContent
     * @return bool
     * @throws CouldNotLocateEncryptionKeyException
     */
    public function writeFileContent(File $file, FileContent $fileContent)
    {
        $key = $this->encryptionKeyRepository->getEncryptionKeyForFile($file);
        $this->encryption->setKey($key);
        $result = $this->encryption->encryptFile($file, $fileContent->getPath());

        if ($result && is_file($file->getFilePath())) {
            $this->updateFileProperties($file->getId(), [
                'size' => filesize($file->getFilePath()),
                'last_edit' => date('Y-m-d H:i:s')
            ]);
        }

        return (boolean) $result;
    }

    /**
     * @param File $file
     * @return bool|FileContent
     * @throws CouldNotLocateEncryptionKeyException
     * @throws \lib\DataTypes\CouldNotReadFileException
     */
    public function readFileContent(File $file)
    {
        $key = $this->encryptionKeyRepository->getEncryptionKeyForFile($file);
        $this->encryption->setKey($key);

        $path = $this->encryption->decryptFile($file);

        if ($path) {
            return new FileContent($path);
        } else {
            return false;
        }
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

        $sql = "SELECT
                    *
                FROM files
                WHERE parent_id ". (is_null($location) ? 'is' : '=') ." ?
                AND user_id = ?
                ORDER BY (
                CASE
                    WHEN type = 'DIRECTORY' THEN 1
                    WHEN type = 'LINK' THEN 2
                    ELSE 4
                END), " . $this->sort->getSortString();

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
        $fileQuery = $this->pdo->prepare('SELECT * FROM files WHERE user_id = ? AND name = ? AND parent_id '. (is_null($location) ? 'is' : '=') .' ?');
        $fileQuery->execute([$user->getId(), $name, $location]);

        return $this->createFileObject($fileQuery->fetch());
    }

    public function findByFileExtension(User $user, $location, array $fileExtensions)
    {
        $in  = str_repeat('?,', count($fileExtensions) - 1) . '?';

        $params = array_merge([$user->getId(), $location ?? null], $fileExtensions);
        $files = [];

        $SQL = "SELECT *, SUBSTRING_INDEX(name, '.', -1) AS ext 
                FROM files
                WHERE user_id = ? AND parent_id ". (is_null($location) ? 'is' : '=') ." ? AND
                type = 'FILE'
                HAVING ext IN ($in)
                ORDER BY " .  $this->sort->getSortString();

        $statement = $this->pdo->prepare($SQL);
        $statement->execute($params);
        $result = $statement->fetchAll();

        foreach ($result as $file) {
            $files[] = $this->createFileObject($file);
        }

        return new FileList($files);
    }

    public function searchForFile(User $user, string $fileNameSearch, array $fileExtensions, string $fileType, bool $onlyShared)
    {
        $files = [];

        $whereCriteria = [];
        $parametersToBind = [];

        $whereCriteria[] = ' f.user_id = :userId ';
        $parametersToBind[':userId'] = $user->getId();

        if (!empty($fileNameSearch)) {
            $whereCriteria[] = ' f.name LIKE :fileName ';
            $parametersToBind[':fileName'] = '%'. $fileNameSearch .'%';
        }

        if (!empty($fileExtensions)) {
            $extensionsWheres = [];

            foreach ($fileExtensions as $extension) {
                $extensionsWheres[] = ' f.name LIKE ' . $this->pdo->quote('%.' . $extension);
            }

            $whereCriteria[] = ' ('. implode(' OR ', $extensionsWheres) .') ';
        }

        if (!empty($fileType)) {
            $whereCriteria[] = ' f.type = :fileType ';
            $parametersToBind[':fileType'] = $fileType;
        }

        if ($onlyShared) {
            $whereCriteria[] = ' f.encryption = "TOKEN" ';
        }

        $where = implode('AND', $whereCriteria);

        $SQL = "SELECT f.* from files f
                WHERE {$where}
                ORDER BY (
                CASE
                    WHEN type = 'DIRECTORY' THEN 1
                    WHEN type = 'LINK' THEN 2
                    ELSE 4
                END), " .  $this->sort->getSortString();

        $searchStatement = $this->pdo->prepare($SQL);

        foreach ($parametersToBind as $key => $param) {
            $searchStatement->bindValue($key, $param);
        }

        $searchStatement->execute();
        $searchResult = $searchStatement->fetchAll();

        foreach ($searchResult as $file) {
            $files[] = $this->createFileObject($file);
        }

        return new FileList($files, true);
    }

    public function findTotalSizeForUser(User $user)
    {
        $SQL = "SELECT SUM(size) as sizeSum FROM files WHERE user_id = ?";
        $statement = $this->pdo->prepare($SQL);

        if ($statement->execute([$user->getId()])) {
            $result = $statement->fetch();
            return $result['sizeSum'] ?? 0;
        } else {
            return false;
        }
    }

    /**
     * @param array $fileData
     * @return AbstractFile
     */
    private function createFileObject($fileData)
    {
        if ($fileData) {
            if ($fileData['type'] === self::TYPE_FILE) {
                $file = new File($this, $this->userRepository, $fileData);
            } else if ($fileData['type'] === self::TYPE_DIRECTORY) {
                $file = new Directory($this, $this->userRepository, $fileData);
            } else if ($fileData['type'] === self::TYPE_LINK) {
                $file = new Link($this, $this->userRepository, $fileData);
            }
        } else {
            $file = new File($this, $this->userRepository);
        }

        return $file;
    }

    public static function convertBytesToReadable(int $bytes, int $precision = 0)
    {
        $siPrefix = Config::getInstance()->presentation->siprefix;
        $thresh = $siPrefix ? 1000 : 1024;

        if ($bytes < $thresh) {
            return $bytes . ' B';
        }

        $units = $siPrefix ? ['kB','MB','GB','TB','PB','EB','ZB','YB'] : ['KiB','MiB','GiB','TiB','PiB','EiB','ZiB','YiB'];

        $u = -1;

        do {
            $bytes /= $thresh;
            $u++;
        } while ($bytes >= $thresh && $u < count($units) - 1);

        return round($bytes, $precision) . ' ' . $units[$u];
    }
}