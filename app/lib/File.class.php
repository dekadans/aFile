<?php

namespace app\lib;

class File {
    protected $id;
    protected $user;
    protected $name;
    protected $location;
    protected $mime;
    protected $type;
    protected $encryption;
    protected $last_edit;
    protected $created;

    // WORK IN PROGRESS

    public function __construct($id) {
        $checkFile = Registry::get('db')->getPDO()->prepare('SELECT * FROM files WHERE id = ?');
        $checkFile->execute([$id]);
        $fileData = $checkFile->fetch();

        if ($fileData) {
            $this->id = $fileData['id'];
            $this->user = new User($fileData['user_id']);
            $this->name = $fileData['name'];
            $this->location = $fileData['location'];
            $this->mime = $fileData['mime'];
            $this->type = $fileData['type'];
            $this->encryption = $fileData['encryption'];
            $this->last_edit = $fileData['last_edit'];
            $this->created = $fileData['created'];
        }
        else {
            $this->id = '0';
        }
    }

    /**
     * PUBLIC
     */

     /**
      * Deletes the File
      * @return boolean
      */
     public function delete() {
         $filePath = __DIR__ . '/' . Registry::get('config')->files->path . $this->id;
         @unlink($filePath);
         $deleteFile = Registry::get('db')->getPDO()->prepare('DELETE FROM files WHERE id = ?');

         if ($deleteFile->execute([$this->id])) {
             $this->id = null;
             return true;
         }
         else {
             return false;
         }
     }

    /**
     * Reads and returns the contents of the File
     * @return blob
     */
    public function read() {
        $filePath = __DIR__ . '/' . Registry::get('config')->files->path . $this->id;

        if ($this->encryption == 'PERSONAL') {
            $encryption = new Encryption(Registry::get('user')->getKey());
        }
        else {
            // Should be expanded so that it decrypts with token keys.
            return false;
        }

        $fh = fopen($filePath, 'rb');
        $encContent = fread($fh, filesize($filePath));
        fclose($fh);

        if ($encContent) {
            $content = $encryption->decrypt($encContent);
            return $content;
        }
        else {
            return false;
        }
    }

    /**
     * Renames the file, if another file with that user/name/location combo doesn't exist
     * @param  string $newName
     * @return boolean
     */
    public function rename($newName) {
        if (!$this->exists($this->user, $newName, $this->location)) {
            return $this->update(['name' => $newName]);
        }
        else {
            return false;
        }
    }

    /**
     * Writes data to the file
     * @param  blob $content
     * @return boolean
     */
    public function write($content) {
        $filePath = __DIR__ . '/' . Registry::get('config')->files->path . $this->id;

        if ($this->encryption == 'PERSONAL') {
            $encryption = new Encryption(Registry::get('user')->getKey());
        }
        else {
            // Should be expanded so that it encrypts with token keys.
            return false;
        }

        $encContent = $encryption->encrypt($content);

        $fh = fopen($filePath, 'wb');
        $result = fwrite($fh, $encContent);
        fclose($fh);

        if ($result) {
            $this->update([
                'size' => $result,
                'last_edit' => date('Y-m-d H:i:s')
            ]);
        }

        return (boolean) $result;
    }

    /**
     * PRIVATE
     */

     /**
      * Updates columns in files database
      * @param  array $data
      * @return boolean
      */
     private function update($data) {
         $sets = [];

         foreach ($data as $column => $value) {
             if (!is_int($value)) {
                 $value = "'" . $value . "'";
             }

             $sets[] = $column . '=' . $value;
         }
         $sets = implode(', ',$sets);

         $sql = 'UPDATE files SET ' . $sets . ' WHERE id = ?';
         $updateFile = Registry::get('db')->getPDO()->prepare($sql);
         try {
             return $updateFile->execute([$this->id]);
         }
         catch (PDOException $e) {
             return false;
         }
     }

    /**
     *  STATIC
     */

    /**
     * Creates a new file in the database and returns File object for it
     * @param  user $user
     * @param  string $name
     * @param  string $location
     * @param  string $mime
     * @param  blob $content
     * @return File | boolean
     */
    public static function createFile(User $user, $name, $location, $mime, $content) {
        if (!self::exists($user, $name, $location)) {
            $addFile = Registry::get('db')->getPDO()->prepare('INSERT INTO files (user_id, name, location, mime, type) VALUES (?,?,?,?,?)');

            try {
                if ($addFile->execute([$user->getId(), $name, $location, $mime, 'FILE'])) {
                    $file = new self(Registry::get('db')->getPDO()->lastInsertId());
                    $file->write($content);
                    return $file;
                }
                else {
                    return false;
                }
            }
            catch (\PDOException $e) {
                return false;
            }
        }
        else {
            return false;
        }
    }

    public static function createDirectory() {

    }

    /**
     * Checks if a file exists in the database
     * @param  user $user
     * @param  string $name
     * @param  string $location
     * @return booelan
     */
    public static function exists(User $user, $name, $location) {
        $checkFile = Registry::get('db')->getPDO()->prepare('SELECT * FROM files WHERE user_id = ? AND name = ? AND location = ?');
        $checkFile->execute([$user->getId(), $name, $location]);
        return $checkFile->fetch() ? true : false;
    }

    /**
     * GETTERS AND SETTERS
     */

    /**
     * Get the value of Id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the value of User
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the value of Name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the value of Location
     *
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Get the value of Mime
     *
     * @return mixed
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * Get the value of Type
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the value of Encryption
     *
     * @return mixed
     */
    public function getEncryption()
    {
        return $this->encryption;
    }

    /**
     * Get the value of Last Edit
     *
     * @return mixed
     */
    public function getLastEdit()
    {
        return $this->last_edit;
    }

    /**
     * Get the value of Created
     *
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

}
