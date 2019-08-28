<?php
namespace lib\Repositories;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\KeyProtectedByPassword;
use lib\DataTypes\AuthenticationToken;
use lib\Database;
use lib\DataTypes\User;

class UserRepository
{
    /** @var \PDO */
    private $pdo;

    /** @var ConfigurationRepository */
    private $config;

    /**
     * UserRepository constructor.
     * @param Database $db
     */
    public function __construct(Database $db, ConfigurationRepository $config)
    {
        $this->pdo = $db->getPDO();
        $this->config = $config;
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
     * @return KeyProtectedByPassword|bool
     */
    public function getProtectedEncryptionKeyForUser($userId)
    {
        $userStatement = $this->pdo->prepare('SELECT encryption_key FROM users WHERE id = ?');
        $userStatement->execute([$userId]);
        $user = $userStatement->fetch();

        if (isset($user['encryption_key'])) {
            return KeyProtectedByPassword::loadFromAsciiSafeString($user['encryption_key']);
        } else {
            return false;
        }
    }

    /**
     * @param AuthenticationToken $authenticationToken
     * @return bool
     */
    public function addAuthTokenToUser(AuthenticationToken $authenticationToken)
    {
        $statement = $this->pdo->prepare('INSERT INTO auth (user_id, selector, hashed_token, expires) VALUES (?,?,?,?);');
        $result = $statement->execute([
            $authenticationToken->getUser()->getId(),
            $authenticationToken->getSelector(),
            $authenticationToken->getHashedToken(),
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
     * @param User $user
     * @return bool
     */
    public function deleteAllAuthTokensForUser(User $user)
    {
        $statement = $this->pdo->prepare('DELETE FROM auth WHERE user_id = ?;');
        $result = $statement->execute([$user->getId()]);

        return $result;
    }

    public function deleteExpiredAuthenticationTokens()
    {
        $this->pdo->exec('DELETE FROM auth WHERE expires < NOW();');
    }

    /**
     * @param string $selector
     * @return AuthenticationToken
     */
    public function getAuthenticationTokenBySelector(string $selector)
    {
        $statement = $this->pdo->prepare('SELECT user_id, hashed_token, expires FROM auth WHERE selector = ?');
        $statement->execute([$selector]);
        $row = $statement->fetch();

        if ($row) {
            return new AuthenticationToken(
                $this->getUserById($row['user_id']),
                $selector,
                $row['hashed_token'],
                strtotime($row['expires'])
            );
        }
    }

    /**
     * @param string $oldSelector
     * @param int $newExpiresTimestamp
     * @return bool
     */
    public function refreshAuthenticationToken(string $oldSelector, string $newSelector, int $newExpiresTimestamp)
    {
        $statement = $this->pdo->prepare('UPDATE auth SET selector = :newSelector, expires = :newExpiresDate WHERE selector = :oldSelector;');
        $statement->bindValue(':newSelector', $newSelector);
        $statement->bindValue(':newExpiresDate', date('Y-m-d H:i:s', $newExpiresTimestamp));
        $statement->bindValue(':oldSelector', $oldSelector);

        return $statement->execute();
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

            mkdir(__DIR__ . '/../../../' . $this->config->find('files', 'path') . $lastInsertId);
            return $user;
        }

        return 'User already exists';
    }

    public function updatePassword(User $user, string $oldPassword, string $newPassword)
    {
        $key = $this->getProtectedEncryptionKeyForUser($user->getId());

        $key->changePassword($oldPassword, $newPassword);

        $protectedKey = $key->saveToAsciiSafeString();
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $statement = $this->pdo->prepare("UPDATE users SET password = ?, encryption_key = ? WHERE id = ?;");
        if (!$statement->execute([$newHashedPassword, $protectedKey, $user->getId()])) {
            throw new \RuntimeException('Could not change password.');
        } else {
            return true;
        }
    }

    public function updatePasswordAndKey(User $user, KeyProtectedByPassword $protectedKey, string $newPassword)
    {
        $protectedKeyAscii = $protectedKey->saveToAsciiSafeString();

        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $statement = $this->pdo->prepare("UPDATE users SET password = ?, encryption_key = ? WHERE id = ?;");
        if (!$statement->execute([$newHashedPassword, $protectedKeyAscii, $user->getId()])) {
            throw new \RuntimeException('Could not change password.');
        } else {
            return true;
        }
    }
}