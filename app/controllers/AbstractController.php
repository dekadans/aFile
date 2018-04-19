<?php

namespace controllers;

abstract class AbstractController {
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

    public function parseView($viewName, $params = []) {
        $params['L'] = \lib\Singletons::$language->getLanguageData();
        extract($params);
        require(__dir__ . '/../views/' . $viewName . '.php');
    }

    /**
     * Converts an array to JSON and prints the result.
     * @param  array $data
     */
    public static function outputJSON($data) {
        header('Content-Type:application/json; charset=UTF-8');
        header("Cache-Control: no-cache, must-revalidate");

        echo json_encode($data);
        die;
    }
}
