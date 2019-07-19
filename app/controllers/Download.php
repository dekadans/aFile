<?php

namespace controllers;

use lib\Acl;
use lib\DataTypes\File;
use lib\DataTypes\Link;
use lib\HTTP\DownloadResponse;
use lib\HTTP\HTMLResponse;
use lib\HTTP\Response;
use lib\HTTP\TemplateResponse;
use lib\Repositories\FileRepository;
use lib\Translation;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Download extends AbstractController {
    /** @var FileRepository  */
    private $fileRepository;
    /** @var File|Link */
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
            return $this->fileNotFound();
        }

        $access = Acl::checkDownloadAccess($this->file, $this->urlToken, $this->password);

        if ($access === Acl::DOWNLOAD_ACCESS_DENIED) {
            return $this->accessDenied();
        } else if ($access === Acl::DOWNLOAD_ACCESS_PASSWORD) {
            return $this->filePassword();
        } else {
            if ($this->file->isDownloadable() && file_exists($this->file->getFilePath())) {
                return $this->download();
            } else {
                return (new Response('Could not download file', 500))->psr7();
            }
        }
    }

    private function fileNotFound() : ResponseInterface
    {
        $title = '404 ' . Translation::getInstance()->translate('404_NOT_FOUND');
        $response = new TemplateResponse('downloadNotice', 'partials/fileNotFound', $title, [], 404);
        return $response->psr7();
    }

    private function accessDenied() : ResponseInterface
    {
        $title = '403 ' . Translation::getInstance()->translate('403_FORBIDDEN');
        $response = new TemplateResponse('downloadNotice', 'partials/accessDenied', $title, [], 403);
        return $response->psr7();
    }

    private function filePassword() : ResponseInterface
    {
        $title = Translation::getInstance()->translate('DOWNLOAD_PASSWORD_TITLE');
        $response = new TemplateResponse('downloadNotice', 'partials/filePassword', $title, [
            'file' => $this->file
        ]);
        return $response->psr7();
    }

    private function download()
    {
        if ($this->forceDownload) {
            return (new DownloadResponse($this->file, false))->psr7();
        } else if ($this->file instanceof Link) {
            return $this->linkRedirect();
        } else if ($editableFile = $this->file->isEditable()) {
            $editableFile->setUrlToken($this->urlToken);
            return (new HTMLResponse('editor', ['editableFile' => $editableFile]))->psr7();
        } else {
            return (new DownloadResponse($this->file, $this->file->isInlineDownload()))->psr7();
        }
    }

    private function linkRedirect() : ResponseInterface
    {
        $title = Translation::getInstance()->translate('LINK_CONFIRM');
        $response = new TemplateResponse('downloadNotice', 'partials/link', $title, [
            'link' => $this->file
        ]);
        return $response->psr7();
    }

    public function getAccessLevel()
    {
        return self::ACCESS_CLOSED;
    }
}
