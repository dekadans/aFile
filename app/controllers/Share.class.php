<?php

namespace controllers;

class Share extends AbstractController {
    protected $file;
    protected $user;

    public function getAccessLevel() {
        return self::ACCESS_LOGIN;
    }

    public function init() {
        $fileId = $this->param('id');
        $this->file = new \lib\File($fileId);
        $this->user = \lib\Registry::get('user');

        if (!$this->file->isset()) {
            $this->outputJSON([
                'error' => 'NO_FILE'
            ]);
        }

        if ($this->file->getUser()->getId() !== $this->user->getId()) {
            $this->outputJSON([
                'error' => 'NO_ACCESS'
            ]);
        }
    }

    public function index() {
    }

    public function actionCreate() {
        $password = $this->param('password');
        $sharingData = $this->file->getSharingInfo();

        if ($password) {
            $column = 'password_token';
            $active = (!empty($sharingData) && ($sharingData['active'] == 'OPEN' || $sharingData['active'] == 'BOTH')) ? 'BOTH' : 'PASSWORD';
            $pwhash = password_hash($password, PASSWORD_DEFAULT);
        }
        else {
            $column = 'open_token';
            $active = (!empty($sharingData) && ($sharingData['active'] == 'PASSWORD' || $sharingData['active'] == 'BOTH')) ? 'BOTH' : 'OPEN';
            $pwhash = $sharingData['password'] ?? null;
        }

        $token = sha1(random_bytes(32));

        if ($sharingData) {
            $sql = "UPDATE share SET {$column}=?, active=?, password=? WHERE file_id = ?";
            $values = [$token, $active, $pwhash, $this->file->getId()];
        }
        else {
            $encryption_key = \Defuse\Crypto\Key::createNewRandomKey();
            $keyAscii = $encryption_key->saveToAsciiSafeString();

            $sql = "INSERT INTO share (file_id, {$column}, active, password, encryption_key) VALUES (?, ?, ?, ?, ?)";
            $values = [$this->file->getId(), $token, $active, $pwhash, $keyAscii];
        }

        // SHOULD PROBABLY CONSIDER THIS ERROR HANDLING MORE
        try {
            $createLink = \lib\Registry::get('db')->getPDO()->prepare($sql);
            if ($createLink->execute($values)) {
                if ($this->file->getEncryption() === 'PERSONAL') {
                    $oldKey = $this->file->getEncryptionKey();
                    $newKey = $keyAscii ?? $sharingData['encryption_key'];
                    $encryption = new \lib\Encryption($oldKey);
                    $encryption->decryptFile($this->file);
                    $encryption->setKey($newKey);
                    $encryption->encryptFile($this->file);

                    $this->file->setEncryption('TOKEN');
                }

                // Return the new data dipshit!!
                $this->outputJSON([
                    'status' => 'ok'
                ]);
            }
            else {
                $this->outputJSON([
                    'error' => 'FAILED'
                ]);
            }
        }
        catch (\Exception $ex) {
            $this->outputJSON([
                'error' => 'FAILED'
            ]);
        }
    }
}
