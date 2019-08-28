<?php

namespace controllers;

use lib\Config;
use lib\DataTypes\AbstractFile;
use lib\HTTP\JsonResponse;
use lib\HTTP\HTMLResponse;
use lib\Repositories\FileRepository;
use lib\Services\AuthenticationService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractController {
    const ACCESS_CLOSED = -1;
    const ACCESS_OPEN = 0;
    const ACCESS_LOGIN = 1;
    const ACCESS_ADMIN = 2;

    /** @var ServerRequestInterface */
    private $request;

    /** @var AuthenticationService */
    private $authenticationService;

    /** @var Config */
    private $config;

    /** @var ContainerInterface */
    private $container;

    abstract public function getAccessLevel();

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->get(ServerRequestInterface::class);
        $this->config  = $container->get(Config::class);
        $this->authenticationService = $container->get(AuthenticationService::class);
    }

    public function checkAccess()
    {
        switch ($this->getAccessLevel()) {
            case self::ACCESS_OPEN:
                return true;
            case self::ACCESS_LOGIN:
                if ($this->authentication()->isSignedIn()) {
                    return true;
                }
                else {
                    return false;
                }
            case self::ACCESS_ADMIN:
                if ($this->authentication()->isSignedIn() && $this->authentication()->getUser()->getType() == 'ADMIN') {
                    return true;
                }
                else {
                    return false;
                }
            default:
                return false;
        }
    }

    /**
     * @param AbstractFile $file
     * @return bool
     */
    public function checkFileAccess(AbstractFile $file) : bool
    {
        if ($this->authenticationService->isSignedIn() && $this->authenticationService->getUser()->getId() === $file->getUser()->getId()) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Returns a value from POST or GET globals
     * @param  string $name
     * @return string
     */
    public function param($name) {
        $post = $this->request->getParsedBody();
        $get = $this->request->getQueryParams();

        if (isset($post[$name])) {
            $value = $post[$name];
        }
        else if (isset($get[$name])) {
            $value = $get[$name];
        }
        else {
            return null;
        }

        if ($value === 'null' || $value === '') {
            return null;
        }
        else {
            return $value;
        }
    }

    /**
     * @param string $viewName
     * @param array $params
     * @return ResponseInterface
     */
    protected function parseView(string $viewName, $params = []) {
        $response = new HTMLResponse($viewName, $params);
        return $response->psr7();
    }

    /**
     * Converts an array to JSON and prints the result.
     * @param  array $data
     * @return ResponseInterface
     */
    protected function outputJSON($data) {
        $response = new JsonResponse($data);
        return $response->psr7();
    }

    /**
     * @return AuthenticationService
     */
    protected function authentication()
    {
        return $this->authenticationService;
    }

    /**
     * @return Config
     */
    protected function config()
    {
        return $this->config;
    }

    /**
     * @return ServerRequestInterface
     */
    protected function getRequest()
    {
        return $this->request;
    }

    /**
     * @return FileRepository
     */
    protected function getFileRepository()
    {
        return $this->container->get(FileRepository::class);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }
}
