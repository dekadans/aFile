<?php

namespace controllers;

use \lib\File;
use lib\FileRepository;
use lib\Registry;

class Upload extends AbstractController {
    private $location;
    private $user;

    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function index() {
        $this->location = $this->param('location');
        $this->user = Registry::get('user');

        $maxsize = Registry::get('config')->files->maxsize;

        $results = [];

        foreach ($_FILES as $file) {
            if ($file['error'] || $file['size'] > $maxsize) {
                $results[] = false;
                continue;
            }

            $name = $this->getUniqueName($file['name']);
            $mime = $this->getMimeType($file['name'], $file['tmp_name']);

            $results[] = File::create($this->user, $name, $this->location, $mime, $file['tmp_name']);
        }

        if (in_array(false, $results)) {
            $this->outputJSON([
                'error' => Registry::$language->translate('UPLOAD_FAILED')
            ]);
        }
        else {
            $this->outputJSON([
                'status' => 'ok'
            ]);
        }
    }

    private function getUniqueName(string $name) {
        if (!FileRepository::exists($this->user, $name, $this->location)) {
            return $name;
        }

        $nameParts = explode('.', $name);
        $extension = array_pop($nameParts);
        $fileName = implode('.', $nameParts);

        return $fileName . '-' . uniqid() . '.' . $extension;
    }

    private function getMimeType(string $filename, string $temporaryLocation)
    {
        $detectedMime = mime_content_type($temporaryLocation);
        $extension = explode('.', $filename);
        $extension = array_pop($extension);

        switch ($extension) {
            case 'docx':
                return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            case 'pptx':
                return 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
            case 'xlsx':
                return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            case 'svg':
                return 'image/svg+xml';
            default:
                return $detectedMime;
        }
    }
}
