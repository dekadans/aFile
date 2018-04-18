<?php
namespace lib\Repositories;


use lib\AuthenticationToken;
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

    public function addAuthTokenToUser(AuthenticationToken $authenticationToken)
    {
        $statement = $this->pdo->prepare('INSERT INTO auth (user_id, selector, hashed_token, encrypted_password, expires) VALUES (?,?,?,?,?);');
        $result = $statement->execute([
            $authenticationToken->getUser()->getId(),
            $authenticationToken->getSelector(),
            $authenticationToken->getHashedToken(),
            $authenticationToken->getEncryptedPassword(),
            date('Y-m-d H:i:s', $authenticationToken->getExpires())
        ]);

        return $result;
    }

    public function deleteAuthTokenForUser(AuthenticationToken $authenticationToken)
    {
        $statement = $this->pdo->prepare('DELETE FROM auth WHERE selector = ?;');
        $result = $statement->execute([$authenticationToken->getSelector()]);

        return $result;
    }

    public function getAuthenticationTokenBySelector($selector)
    {
        $statement = $this->pdo->prepare('SELECT user_id, hashed_token, encrypted_password, expires FROM auth WHERE selector = ?');
        $statement->execute([$selector]);
        $row = $statement->fetch();

        if ($row) {
            return new AuthenticationToken(
                $this->getUserById($row['user_id']),
                $selector,
                $row['hashed_token'],
                $row['encrypted_password'],
                strtotime($row['expires'])
            );
        }
    }


}