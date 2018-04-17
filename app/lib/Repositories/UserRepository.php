<?php
namespace lib\Repositories;


use lib\Database;
use lib\User;

class UserRepository
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct(Database $db)
    {
        $this->pdo = $db->getPDO();
    }

    public function getUserById($userId)
    {
        $userStatement = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $userStatement->execute([$userId]);
        $user = $userStatement->fetch();

        if ($user) {
            return new User($user);
        }
        else {
            return new User(null);
        }
    }

    public function getUserByUsername(string $username)
    {
        $userStatement = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $userStatement->execute([$username]);
        $user = $userStatement->fetch();

        if ($user) {
            return new User($user);
        }
        else {
            return new User(null);
        }
    }

    public function getProtectedEncryptionKeyForUser($userId)
    {
        $userStatement = $this->pdo->prepare('SELECT encryption_key FROM users WHERE id = ?');
        $userStatement->execute([$userId]);
        $user = $userStatement->fetch();

        return $user['encryption_key'] ?? false;
    }

    public function addAuthTokenToUser($userId, $selector, $hashedToken, $encryptedPassword, $expires)
    {
        $statement = $this->pdo->prepare('INSERT INTO auth (user_id, selector, hashed_token, encrypted_password, expires) VALUES (?,?,?,?,?);');
        $result = $statement->execute([$userId, $selector, $hashedToken, $encryptedPassword, $expires]);

        return $result;
    }

    public function deleteAuthTokenForUser($userId, $selector)
    {
        $statement = $this->pdo->prepare('DELETE FROM auth WHERE user_id = ? AND selector = ?;');
        $result = $statement->execute([$userId, $selector]);

        return $result;
    }

    public function getAuthRowBySelector($selector)
    {
        $statement = $this->pdo->prepare('SELECT user_id, hashed_token, encrypted_password, expires FROM auth WHERE selector = ?');
        $statement->execute([$selector]);
        return $statement->fetch();
    }
}