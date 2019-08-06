<?php

namespace lib\Services;

use lib\DataTypes\Directory;
use lib\DataTypes\File;
use lib\DataTypes\FileContent;
use lib\DataTypes\User;
use lib\Exceptions\CreateFileException;
use lib\Repositories\FileRepository;

class CreateFileService
{
    /** @var FileRepository */
    private $fileRepository;

    public function __construct(FileRepository $fileRepository)
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * @param User $user
     * @param string $name
     * @param $location
     * @param string $mime
     * @param FileContent $fileContent
     * @return File
     * @throws CreateFileException
     * @throws \lib\Repositories\CouldNotLocateEncryptionKeyException
     */
    public function createFile(User $user, string $name, $location, string $mime, FileContent $fileContent)
    {
        /** @var File $file */
        $file = $this->fileRepository->create($user, $name, $location, $mime, 'FILE', 'PERSONAL');

        if ($file) {
            $this->writeContents($file, $fileContent);
        }

        return $file;
    }

    /**
     * @param User $user
     * @param string $name
     * @param $location
     * @param FileContent $fileContent
     * @return File
     * @throws CreateFileException
     * @throws \lib\Repositories\CouldNotLocateEncryptionKeyException
     */
    public function createLink(User $user, string $name, $location, FileContent $fileContent)
    {
        /** @var \lib\DataTypes\File $file */
        $file = $this->fileRepository->create($user, $name, $location, 'text/plain', 'LINK', 'PERSONAL');

        if ($file) {
            $this->writeContents($file, $fileContent);
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
        return $this->fileRepository->create($user, $name, $location, '', 'DIRECTORY', 'NONE');
    }

    /**
     * @param File $file
     * @param FileContent $fileContent
     * @throws CreateFileException
     * @throws \lib\Repositories\CouldNotLocateEncryptionKeyException
     */
    private function writeContents(File $file, FileContent $fileContent)
    {
        if (!$this->fileRepository->writeFileContent($file, $fileContent)) {
            $this->fileRepository->deleteFile($file->getId());
            throw new CreateFileException('Could not write file. Check directory permissions.');
        }
    }
}