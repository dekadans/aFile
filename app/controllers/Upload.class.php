<?php

namespace controllers;

use \lib\File;

class Upload extends AbstractController {
    protected $location;
    protected $user;

    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        $this->location = $this->param('location');
        $this->user = \lib\Registry::get('user');

        $results = [];

        foreach ($_FILES as $file) {
            $name = $this->getUniqueName($file['name']);
            $mime = mime_content_type($file['tmp_name']);

            $results[] = File::createFile($this->user, $name, $this->location, $mime, $file['tmp_name']);
        }

        if (in_array(false, $results)) {
            $this->outputJSON([
                'error' => 'UPLOAD_FAILED'
            ]);
        }
        else {
            $this->outputJSON([
                'status' => 'ok'
            ]);
        }
    }

    private function getUniqueName($name) {
        if (!File::exists($this->user, $name, $this->location)) {
            return $name;
        }

        $nameParts = explode('.', $name);
        $extension = array_pop($nameParts);
        $fileName = implode('.', $nameParts);

        return $fileName . '-' . uniqid() . '.' . $extension;
    }
}
