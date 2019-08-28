<?php
namespace lib\Services;

use lib\Config;
use lib\DataTypes\User;
use lib\Repositories\FileRepository;

class SearchService
{
    private $fileName = '';
    private $fileExtensions = [];
    private $fileType = '';
    private $onlyShared = false;

    private $searchString = '';

    /** @var FileRepository */
    private $fileRepository;

    /** @var Config */
    private $config;

    public function __construct(FileRepository $fileRepository, Config $config)
    {
        $this->fileRepository = $fileRepository;
        $this->config = $config;
    }

    public function search(User $user, string $searchString)
    {
        $this->searchString = $searchString;

        $this->setFileExtensions();
        $this->setFileType();
        $this->setOnlyShared();

        $this->setFileName();

        if ($this->isReadyForSearch()) {
            return $this->fileRepository->searchForFile($user, $this->fileName, $this->fileExtensions, $this->fileType, $this->onlyShared);
        } else {
            return false;
        }
    }

    private function setFileExtensions()
    {
        $fileExtensionGroup = $this->extractAdvancedParameter('content');

        if (!is_null($fileExtensionGroup)) {
            $fileExtensions = $this->config->get('type_groups', $fileExtensionGroup);

            if (is_array($fileExtensions)) {
                $this->fileExtensions = $fileExtensions;
            }
        }
    }

    private function setFileType()
    {
        $fileType = strtoupper($this->extractAdvancedParameter('type'));

        switch ($fileType) {
            case FileRepository::TYPE_FILE:
                $this->fileType = FileRepository::TYPE_FILE;
                break;
            case FileRepository::TYPE_DIRECTORY:
                $this->fileType = FileRepository::TYPE_DIRECTORY;
                break;
            case FileRepository::TYPE_LINK:
                $this->fileType = FileRepository::TYPE_LINK;
                break;
            default:
                $this->fileType = '';
        }
    }

    private function setOnlyShared()
    {
        $onlyShared = $this->extractAdvancedParameter('shared');

        if ($onlyShared === 'true' || $onlyShared === '1') {
            $this->onlyShared = true;
        }
    }

    private function setFileName()
    {
        $this->fileName = trim($this->searchString);
    }

    private function extractAdvancedParameter(string $parameter)
    {
        if (preg_match('/'. $parameter .':([A-Za-z0-9]{1,})/', $this->searchString, $matches)) {
            $parameterValue = $matches[1];

            $this->searchString = trim(preg_replace('/'. $parameter .':[A-Za-z0-9]{1,}/', '', $this->searchString));
        }

        return $parameterValue ?? null;
    }

    private function isReadyForSearch()
    {
        if (empty($this->fileName) &&
            empty($this->fileExtensions) &&
            empty($this->fileType) &&
            $this->onlyShared === false) {

            return false;
        } else {
            return true;
        }
    }
}