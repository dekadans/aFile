<?php

namespace lib;

use lib\HTTP\DownloadResponse;
use lib\HTTP\Response;
use lib\Repositories\FileRepository;

class Download {
    /** @var File */
    protected $file;
    /** @var string */
    private $id;
    /** @var string */
    private $token;

    public function __construct(string $id, string $token) {
        $fileRepository = new FileRepository();
        $this->file = $fileRepository->findByUniqueString($id);
        $this->id = $id;
        $this->token = $token ?? '';
    }

    /**
     * @return Response
     */
    public function download() {
        if (!$this->file->isset()) {
            return new Response('Not Found', 404);
        }

        if (!Acl::checkDownloadAccess($this)) {
            return new Response('Access Denied', 401);
        }

        $encryptionKey = $this->file->getEncryptionKey();

        if ($this->file->isFile() && file_exists($this->file->getFilePath()) && $encryptionKey) {
            $encryption = new Encryption($encryptionKey);
            $encryption->decryptFile($this->file);

            return new DownloadResponse($this->file);
        }
        else {
            return new Response('Could not download file', 500);
        }
    }

    /**
     * Get the value of File
     *
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }
}
