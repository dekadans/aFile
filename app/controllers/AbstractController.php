<?php

namespace controllers;

use lib\HTTP\JsonResponse;
use lib\HTTP\HTMLResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractController {
    const ACCESS_CLOSED = -1;
    const ACCESS_OPEN = 0;
    const ACCESS_LOGIN = 1;
    const ACCESS_ADMIN = 2;

    /** @var ServerRequestInterface */
    protected $request;

    abstract public function getAccessLevel();

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Returns a value from POST or GET globals
     * @param  string $name
     * @return string
     */
    public function param($name) {
        $post = $this->request->getParsedBody();
        //$postArray = filter_input(INPUT_POST, $name, FILTER_DEFAULT , FILTER_REQUIRE_ARRAY);
        $get = $this->request->getQueryParams();

        if (isset($post[$name])) {
            $value = $post[$name];
        }
        else if (isset($get[$name])) {
            $value = $get[$name];
        }
        /*else if (!empty($postArray)) {
            return $postArray;
        }*/
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
    protected function outputJSON(array $data) {
        $response = new JsonResponse($data);
        return $response->psr7();
    }
}
