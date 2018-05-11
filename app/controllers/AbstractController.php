<?php

namespace controllers;

use lib\HTTP\JsonResponse;
use lib\HTTP\HTMLResponse;

abstract class AbstractController {
    const ACCESS_CLOSED = -1;
    const ACCESS_OPEN = 0;
    const ACCESS_LOGIN = 1;
    const ACCESS_ADMIN = 2;

    abstract public function getAccessLevel();

    /**
     * Returns a value from POST or GET globals
     * @param  string $name
     * @return string
     */
    public function param($name) {
        $post = filter_input(INPUT_POST, $name);
        $postArray = filter_input(INPUT_POST, $name, FILTER_DEFAULT , FILTER_REQUIRE_ARRAY);
        $get = filter_input(INPUT_GET, $name);

        if (!empty($post)) {
            $value = $post;
        }
        else if (!empty($get)) {
            $value = $get;
        }
        else if (!empty($postArray)) {
            return $postArray;
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
     * @return HTMLResponse
     */
    protected function parseView(string $viewName, $params = []) {
        $response = new HTMLResponse($viewName, $params);
        return $response;
    }

    /**
     * Converts an array to JSON and prints the result.
     * @param  array $data
     * @return JsonResponse
     */
    protected function outputJSON(array $data) {
        return new JsonResponse($data);
    }
}
