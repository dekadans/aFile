<?php

namespace controllers;

use lib\Acl;
use lib\DataTypes\File;
use lib\HTTP\DownloadResponse;
use lib\HTTP\HTMLResponse;
use lib\HTTP\Response;
use lib\Repositories\FileRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Download extends AbstractController {
    /** @var FileRepository  */
    private $fileRepository;
    /** @var File */
    protected $file;
    /** @var string */
    private $id;
    /** @var string */
    private $urlToken;
    /** @var string */
    private $password;
    /** @var bool */
    private $forceDownload = false;

    public function __construct(ServerRequestInterface $request)
    {
        parent::__construct($request);

        $pathInfo = $this->request->getServerParams()['PATH_INFO'] ?? '';

        $pathInfo = substr($pathInfo, 1) . '/';
        list($id, $urlToken) = explode('/', $pathInfo);

        $this->fileRepository = new FileRepository();
        $this->file = $this->fileRepository->findByUniqueString($id);
        $this->id = $id;
        $this->urlToken = $urlToken ?? '';

        $this->password = $this->param('password') ?? '';

        $this->forceDownload = ($this->param('fdl') === '1');
    }

    /**
     * @return ResponseInterface
     */
    public function index() : ResponseInterface
    {
        if (!$this->file->isset()) {
            return (new HTMLResponse('fileNotFound', [], 404))->psr7();
        }

        $access = Acl::checkDownloadAccess($this->file, $this->urlToken, $this->password);

        if ($access === Acl::DOWNLOAD_ACCESS_DENIED) {
            return (new HTMLResponse('accessDenied', [], 403))->psr7();
        } else if ($access === Acl::DOWNLOAD_ACCESS_PASSWORD) {
            return (new HTMLResponse('filePassword', ['file' => $this->file]))->psr7();
        } else {
            if ($this->file->isFile() && file_exists($this->file->getFilePath())) {
                return $this->download();
            } else {
                return (new Response('Could not download file', 500))->psr7();
            }
        }
    }

    private function download()
    {
        if ($this->forceDownload) {
            return (new DownloadResponse($this->file, false))->psr7();
        } else if ($editableFile = $this->file->isEditable()) {
            $editableFile->setUrlToken($this->urlToken);
            return (new HTMLResponse('editor', ['editableFile' => $editableFile]))->psr7();
        } else {
            return (new DownloadResponse($this->file, $this->file->isInlineDownload()))->psr7();
        }
    }

    public function getAccessLevel()
    {
        return self::ACCESS_CLOSED;
    }
}
