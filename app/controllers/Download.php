<?php

namespace controllers;

use lib\DataTypes\EditableFile;
use lib\DataTypes\File;
use lib\DataTypes\FileToken;
use lib\DataTypes\Link;
use lib\HTTP\DownloadResponse;
use lib\HTTP\HTMLResponse;
use lib\HTTP\Response;
use lib\HTTP\TemplateResponse;
use lib\Repositories\EncryptionKeyRepository;
use lib\Repositories\FileRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class Download extends AbstractController {

    const DOWNLOAD_ACCESS_APPROVED = 1;
    const DOWNLOAD_ACCESS_PASSWORD = 2;
    const DOWNLOAD_ACCESS_DENIED = 3;

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

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $pathInfo = $this->getRequest()->getServerParams()['PATH_INFO'] ?? '';

        $pathInfo = substr($pathInfo, 1) . '/';
        list($id, $urlToken) = explode('/', $pathInfo);

        $this->fileRepository = $this->getFileRepository();
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

        $access = $this->checkDownloadAccess();

        if ($access === self::DOWNLOAD_ACCESS_DENIED) {
            return $this->accessDenied();
        } else if ($access === self::DOWNLOAD_ACCESS_PASSWORD) {
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
        $title = '404 ' . $this->translation()->translate('404_NOT_FOUND');
        $response = new TemplateResponse('downloadNotice', 'partials/fileNotFound', $title, ['lang' => $this->translation()], 404);
        return $response->psr7();
    }

    private function accessDenied() : ResponseInterface
    {
        $title = '403 ' . $this->translation()->translate('403_FORBIDDEN');
        $response = new TemplateResponse('downloadNotice', 'partials/accessDenied', $title, ['lang' => $this->translation()], 403);
        return $response->psr7();
    }

    private function filePassword() : ResponseInterface
    {
        $title = $this->translation()->translate('DOWNLOAD_PASSWORD_TITLE');
        $response = new TemplateResponse('downloadNotice', 'partials/filePassword', $title, [
            'file' => $this->file,
            'lang' => $this->translation()
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
            return (new HTMLResponse('editor', $this->getEditorData($editableFile)))->psr7();
        } else {
            return (new DownloadResponse($this->file, $this->file->isInlineDownload()))->psr7();
        }
    }

    private function getEditorData(EditableFile $editableFile)
    {
        $editableFile->setUrlToken($this->urlToken);
        $file = [
            'id' => $this->file->getId(),
            'name' => $this->file->getName(),
            'date' => $this->file->getReadableDate($this->translation()),
            'code' => $editableFile->isCode(),
            'markdown' => $editableFile->isMarkdown(),
            'text' => $editableFile->getText(),
            'downloadLink' => $editableFile->getForceDownloadLink(),
            'editable' => $this->checkFileAccess($this->file)
        ];
        return [
            'file' => json_encode($file)
        ];
    }

    private function linkRedirect() : ResponseInterface
    {
        $title = $this->translation()->translate('LINK_CONFIRM');
        $response = new TemplateResponse('downloadNotice', 'partials/link', $title, [
            'link' => $this->file,
            'lang' => $this->translation()
        ]);
        return $response->psr7();
    }

    public function getAccessLevel()
    {
        return self::ACCESS_CLOSED;
    }

    /**
     * Checks access to a requested download
     * @param File $file
     * @param string $urlToken
     * @param string $password
     * @return int
     */
    private function checkDownloadAccess() : int
    {
        if ($this->authentication()->isSignedIn() && $this->authentication()->getUser()->getId() == $this->file->getUser()->getId()) {
            return self::DOWNLOAD_ACCESS_APPROVED;
        }
        else {
            /** @var EncryptionKeyRepository $encryptionKeyRepository */
            $encryptionKeyRepository = $this->getContainer()->get(EncryptionKeyRepository::class);
            $fileToken = $encryptionKeyRepository->findAccessTokenForFile($this->file);

            if ($fileToken && $fileToken->getToken() === $this->urlToken) {
                if ($fileToken->getActiveState() === FileToken::STATE_OPEN) {
                    return self::DOWNLOAD_ACCESS_APPROVED;
                } else if ($fileToken->getActiveState() === FileToken::STATE_RESTRICTED && !empty($fileToken->getPasswordHash())) {
                    if (!empty($this->password) && password_verify($this->password, $fileToken->getPasswordHash())) {
                        return self::DOWNLOAD_ACCESS_APPROVED;
                    } else {
                        return self::DOWNLOAD_ACCESS_PASSWORD;
                    }
                }
            }
        }

        return self::DOWNLOAD_ACCESS_DENIED;
    }
}
