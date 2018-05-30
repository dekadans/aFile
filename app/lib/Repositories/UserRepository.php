<?php
namespace lib\Repositories;


use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\KeyProtectedByPassword;
use lib\AuthenticationToken;
use lib\Config;
use lib\Database;
use lib\User;

class UserRepository
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * UserRepository constructor.
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->pdo = $db->getPDO();
    }

    /**
     * @param int $userId
     * @return User
     */
    public function getUserById($userId)
    {
        $userStatement = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $userStatement->execute([$userId]);
        $user = $userStatement->fetch();

        if ($user) {
            return new User($user);
        }
        else {
            return new User([]);
        }
    }

    /**
     * @param string $username
     * @return User
     */
    public function getUserByUsername(string $username)
    {
        $userStatement = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $userStatement->execute([$username]);
        $user = $userStatement->fetch();

        if ($user) {
            return new User($user);
        }
        else {
            return new User([]);
        }
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function getProtectedEncryptionKeyForUser($userId)
    {
        $userStatement = $this->pdo->prepare('SELECT encryption_key FROM users WHERE id = ?');
        $userStatement->execute([$userId]);
        $user = $userStatement->fetch();

        return $user['encryption_key'] ?? false;
    }

    /**
     * @param AuthenticationToken $authenticationToken
     * @return bool
     */
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

    /**
     * @param AuthenticationToken $authenticationToken
     * @return bool
     */
    public function deleteAuthTokenForUser(AuthenticationToken $authenticationToken)
    {
        $statement = $this->pdo->prepare('DELETE FROM auth WHERE selector = ?;');
        $result = $statement->execute([$authenticationToken->getSelector()]);

        return $result;
    }

    /**
     * @param string $selector
     * @return AuthenticationToken
     */
    public function getAuthenticationTokenBySelector(string $selector)
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

    /**
     * @param string $username
     * @param string $password
     * @return User|string
     */
    public function createUser(string $username, string $password)
    {
        if (!$this->getUserByUsername($username)->isset()) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            try {
                $encryptionKey = KeyProtectedByPassword::createRandomPasswordProtectedKey($password)->saveToAsciiSafeString();
            } catch (EnvironmentIsBrokenException $e) {
                return $e->getMessage();
            }

            $statement = $this->pdo->prepare("INSERT INTO users (username, password, encryption_key, account_type) VALUES (?,?,?,'USER')");
            if (!$statement->execute([$username, $hashedPassword, $encryptionKey])) {
                return 'Failed to insert to database';
            }

            $lastInsertId = $this->pdo->lastInsertId();
            $user = $this->getUserById($lastInsertId);

            mkdir(__DIR__ . '/../../../' . Config::getInstance()->files->path . $lastInsertId);
            return $user;
        }

        return 'User already exists';
    }


}