<?php

namespace lib;

use lib\HTTP\DownloadResponse;
use lib\HTTP\HTMLResponse;
use lib\HTTP\Response;
use lib\Repositories\FileRepository;
use Psr\Http\Message\ResponseInterface;

class Download {
    /** @var File */
    protected $file;
    /** @var string */
    private $id;
    /** @var string */
    private $token;

    public function __construct(string $id, string $token)
    {
        $fileRepository = new FileRepository();
        $this->file = $fileRepository->findByUniqueString($id);
        $this->id = $id;
        $this->token = $token ?? '';
    }

    /**
     * @return ResponseInterface
     */
    public function download() : ResponseInterface
    {
        if (!$this->file->isset()) {
            return (new HTMLResponse('fileNotFound', [], 404))->psr7();
        }

        if (!Acl::checkDownloadAccess($this)) {
            return (new HTMLResponse('accessDenied', [], 403))->psr7();
        }

        $encryptionKey = $this->file->getEncryptionKey();

        if ($this->file->isFile() && file_exists($this->file->getFilePath()) && $encryptionKey) {
            $encryption = new Encryption($encryptionKey);
            $encryption->decryptFile($this->file);

            return (new DownloadResponse($this->file))->psr7();
        }
        else {
            return (new Response('Could not download file', 500))->psr7();
        }
    }

    /**
     * @return File
     */
    public function getFile() : File
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
