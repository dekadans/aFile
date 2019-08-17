<?php

namespace lib\Services;

use Defuse\Crypto\KeyProtectedByPassword;
use lib\DataTypes\AuthenticationCookie;
use lib\DataTypes\AuthenticationToken;
use lib\DataTypes\User;
use lib\Exceptions\AuthenticationException;
use lib\Repositories\UserRepository;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationService
{
    /** @var User|null */
    private $signedInUser = null;

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
                    $this->userRepository->deleteExpiredAuthenticationTokens();

                    $user->setKey($encryptionKey);
                    $this->createCookie($user);

                    $this->signedInUser = $user;
                    return true;
                }
            }
        }

        return false;
    }

    public function deauthenticate(ServerRequestInterface $request)
    {
        $cookie = AuthenticationCookie::createFromRequest($request);

        if ($cookie) {
            $authTokenInDb = $this->userRepository->getAuthenticationTokenBySelector($cookie->getSelector());

            if ($authTokenInDb) {
                $this->userRepository->deleteAuthTokenForUser($authTokenInDb);
            }

            $cookie->clear();
            $this->signedInUser = null;
        }
    }

    public function load(ServerRequestInterface $request)
    {
        if (is_null($this->signedInUser)) {
            $cookie = AuthenticationCookie::createFromRequest($request);

            if ($cookie !== false) {
                // Authenticate using cookie
                $authTokenInDb = $this->userRepository->getAuthenticationTokenBySelector($cookie->getSelector());
                $hashedTokenInCookie = hash('sha256', $cookie->getEncryptionKey());

                if ($authTokenInDb->getUser()->isset() && hash_equals($authTokenInDb->getHashedToken(), $hashedTokenInCookie)) {
                    if ($authTokenInDb->getExpires() > time()) {
                        $user = $authTokenInDb->getUser();
                        $user->setKey($cookie->getEncryptionKey());
                        $this->signedInUser = $user;

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

    private function createCookie(User $user)
    {
        $selector = bin2hex(random_bytes(6));

        $hashedToken = hash('sha256', $user->getKey());

        $expires = strtotime('+ 1 MONTH');

        $authToken = new AuthenticationToken(
            $user,
            $selector,
            $hashedToken,
            $expires
        );

        $result = $this->userRepository->addAuthTokenToUser($authToken);

        if ($result) {
            $cookie = new AuthenticationCookie($selector, $user->getKey());
            $cookie->set($expires);
        }
    }

    /**
     * @return bool
     */
    public function isSignedIn() : bool
    {
        return !is_null($this->signedInUser);
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->signedInUser;
    }
}