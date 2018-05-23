<?php

namespace controllers;

use lib\Acl;
use lib\Encryption;
use lib\File;
use lib\HTTP\DownloadResponse;
use lib\HTTP\HTMLResponse;
use lib\HTTP\Response;
use lib\Repositories\FileRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Download extends AbstractController {
    /** @var File */
    protected $file;
    /** @var string */
    private $id;
    /** @var string */
    private $token;

    public function __construct(ServerRequestInterface $request)
    {
        parent::__construct($request);

        $pathInfo = $this->request->getServerParams()['PATH_INFO'] ?? '';

        $pathInfo = substr($pathInfo, 1) . '/';
        list($id, $token) = explode('/', $pathInfo);

        $fileRepository = new FileRepository();
        $this->file = $fileRepository->findByUniqueString($id);
        $this->id = $id;
        $this->token = $token ?? '';
    }

    /**
     * @return ResponseInterface
     */
    public function index() : ResponseInterface
    {
        if (!$this->file->isset()) {
            return (new HTMLResponse('fileNotFound', [], 404))->psr7();
        }

        if (!Acl::checkDownloadAccess($this->file, $this->token)) {
            return (new HTMLResponse('accessDenied', [], 403))->psr7();
        }

        if ($this->file->isFile() && file_exists($this->file->getFilePath()) && $this->file->decrypt()) {
            return (new DownloadResponse($this->file))->psr7();
        }
        else {
            return (new Response('Could not download file', 500))->psr7();
        }
    }

    public function getAccessLevel()
    {
        return self::ACCESS_CLOSED;
    }
}
