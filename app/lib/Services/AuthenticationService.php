<?php

namespace lib\Services;

use lib\Repositories\ConfigurationRepository;
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

    private $stayLoggedIn = true;
    private $tokenLife = '+ 1 MONTH';

    public function __construct(UserRepository $userRepository, ConfigurationRepository $config)
    {
        $this->userRepository = $userRepository;

        if ($config->find('login', 'stay_logged_in') !== '1') {
            $this->stayLoggedIn = false;
            $this->tokenLife = '+ 1 DAY';
        }
    }

    public function authenticate(string $username, string $password)
    {
        $user = $this->userRepository->getUserByUsername($username);

        if ($user->isset()) {
            if (password_verify($password, $user->getHashedPassword())) {
                $this->userRepository->deleteExpiredAuthenticationTokens();
                $this->createCookie($user);
                $this->signedInUser = $user;
                return true;
            }
        }

        return false;
    }

    public function deauthenticate(ServerRequestInterface $request, bool $signOutEverywhere = false)
    {
        $cookie = AuthenticationCookie::createFromRequest($request);

        if ($cookie) {
            $authTokenInDb = $this->userRepository->getAuthenticationTokenBySelector($cookie->getSelector());

            if ($authTokenInDb) {
                if ($signOutEverywhere) {
                    $this->userRepository->deleteAllAuthTokensForUser($authTokenInDb->getUser());
                } else {
                    $this->userRepository->deleteAuthTokenForUser($authTokenInDb);
                }
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
                $hashedTokenInCookie = hash('sha256', $cookie->getToken());

                if ($authTokenInDb && $authTokenInDb->getUser()->isset() && hash_equals($authTokenInDb->getHashedToken(), $hashedTokenInCookie)) {
                    if ($authTokenInDb->getExpires() > time()) {
                        $user = $authTokenInDb->getUser();
                        $this->signedInUser = $user;

                        $this->refreshCookie($cookie);

                        return true;
                    }
                }

                $cookie->clear();
            }
        }

        return false;
    }

    private function createCookie(User $user)
    {
        $selector = bin2hex(random_bytes(6));
        $token = bin2hex(random_bytes(32));

        $hashedToken = hash('sha256', $token);
        $expires = strtotime($this->tokenLife);

        $authToken = new AuthenticationToken(
            $user,
            $selector,
            $hashedToken,
            $expires
        );

        $result = $this->userRepository->addAuthTokenToUser($authToken);

        if ($result) {
            $cookie = new AuthenticationCookie($selector, $token);
            $cookie->set($this->stayLoggedIn ? $expires : 0);
        }
    }

    private function refreshCookie(AuthenticationCookie $cookie)
    {
        if (!isset($_SESSION['aFile_CookieRefreshed']) && $this->stayLoggedIn) {
            $newSelector = bin2hex(random_bytes(6));
            $oldSelector = $cookie->getSelector();
            $newExpiresDate = strtotime($this->tokenLife);

            $cookie->setSelector($newSelector);
            $cookie->set($newExpiresDate);

            $this->userRepository->refreshAuthenticationToken($oldSelector, $newSelector, $newExpiresDate);

            $_SESSION['aFile_CookieRefreshed'] = true;
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
