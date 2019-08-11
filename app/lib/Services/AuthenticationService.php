<?php

namespace lib\Services;

use Defuse\Crypto\KeyProtectedByPassword;
use lib\DataTypes\AuthenticationCookie;
use lib\DataTypes\User;
use lib\Exceptions\AuthenticationException;
use lib\Repositories\UserRepository;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationService
{
    const USER_SESSION_NAME = 'aFile_User';

    private static $signedInUser = null;

    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticate(string $username, string $password)
    {
        $user = $this->userRepository->getUserByUsername($username);

        if ($user->isset()) {
            if (password_verify($password, $user->getHashedPassword())) {
                $encryptionKey = $this->findEncryptionKeyForUser($user, $password);

                if ($encryptionKey) {
                    // $this->createCookie($user);
                    $this->userRepository->deleteExpiredAuthenticationTokens();

                    $user->setKey($encryptionKey);
                    self::$signedInUser = $user;
                    return true;
                }
            }
        }

        return false;
    }

    public function load(ServerRequestInterface $request)
    {
        if (is_null(self::$signedInUser)) {
            $cookie = AuthenticationCookie::createFromRequest($request);

            if ($cookie !== false) {
                // Authenticate using cookie
                $authTokenInDb = $this->userRepository->getAuthenticationTokenBySelector($cookie->getSelector());
                $hashedTokenInCookie = hash('sha256', $cookie->getToken());

                if ($authTokenInDb->getUser()->isset() && hash_equals($authTokenInDb->getHashedToken(), $hashedTokenInCookie)) {
                    if ($authTokenInDb->getExpires() > time()) {
                        $user = $authTokenInDb->getUser();
                        $user->setKey($cookie->getEncryptionKey());
                        self::$signedInUser = $user;

                        $newExpiresDate = strtotime('+ 1 MONTH');
                        $cookie->set($newExpiresDate);
                        $this->userRepository->refreshAuthenticationToken($authTokenInDb->getSelector(), $newExpiresDate);

                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function findEncryptionKeyForUser(User $user, string $password)
    {
        $protectedKey = $this->userRepository->getProtectedEncryptionKeyForUser($user->getId());

        if ($protectedKey) {
            try {
                $protectedKeyObject = KeyProtectedByPassword::loadFromAsciiSafeString($protectedKey);
                $keyObject = $protectedKeyObject->unlockKey($password);
                $keyAscii = $keyObject->saveToAsciiSafeString();

                return $keyAscii;
            }
            catch (\Exception $e) {
                throw new AuthenticationException('Could not sign in. Error caught with message: ' . $e->getMessage());
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function isSignedIn() : bool
    {
        return !is_null(self::$signedInUser);
    }

    /**
     * @return User
     */
    public static function getUser()
    {
        return self::$signedInUser;
    }
}