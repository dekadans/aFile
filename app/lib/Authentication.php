<?php
namespace lib;


use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use Defuse\Crypto\KeyProtectedByPassword;
use lib\Repositories\UserRepository;
use Psr\Http\Message\ServerRequestInterface;

class Authentication
{
    const SESSION_USER_ID = 'aFile_User';
    const SESSION_USER_KEY = 'aFile_User_Key';
    const COOKIE_REMEMBER = 'afile_rememberme';

    private static $signedInUser;

    /** @var UserRepository */
    private $userRepository;
    /** @var bool */
    private $isRememberMeActivated;
    /** @var ServerRequestInterface */
    private $request;

    public function __construct(UserRepository $userRepository, ServerRequestInterface $request, bool $isRememberMeActivated)
    {
        $this->userRepository = $userRepository;
        $this->isRememberMeActivated = $isRememberMeActivated;
        $this->request = $request;
    }

    public function authenticate(string $username, string $password)
    {
        $user = $this->userRepository->getUserByUsername($username);

        if ($user->isset()) {
            if (password_verify($password, $user->getHashedPassword())) {
                return $this->signInUser($user, $password);
            }
        }

        return false;
    }

    public function logout()
    {
        unset($_SESSION[self::SESSION_USER_ID]);
        unset($_SESSION[self::SESSION_USER_KEY]);
        session_regenerate_id();

        if (isset($this->request->getCookieParams()[self::COOKIE_REMEMBER])) {
            $token = $this->getAuthenticationTokenFromCookie();
            $this->clearRememberMeCookie($token);
        }

        self::$signedInUser = null;
    }

    public function rememberMe(string $password)
    {
        if (!is_null(self::$signedInUser) && $this->isRememberMeActivated) {
            try {
                $encryptionKey = Key::createNewRandomKey();
                $token = $encryptionKey->saveToAsciiSafeString();
                $encryptedPassword = Crypto::encrypt($password, $encryptionKey);

                $selector = bin2hex(random_bytes(6));

                $hashedToken = hash('sha256', $token);
                $expires = strtotime('+ 1 MONTH');

                $authToken = new AuthenticationToken(
                    self::$signedInUser,
                    $selector,
                    $hashedToken,
                    $encryptedPassword,
                    $expires
                );

                $result = $this->userRepository->addAuthTokenToUser($authToken);

                if ($result) {
                    setcookie(self::COOKIE_REMEMBER, $selector . ':' . $token, $expires, '/');
                }
            } catch (EnvironmentIsBrokenException $e) {
            }
        }
    }

    /**
     * Signs in a user based on authentication cookie
     */
    private function authenticateUsingCookie()
    {
        $authenticationToken = $this->getAuthenticationTokenFromCookie();

        if ($authenticationToken) {
            $hashedTokenFromCookie = hash('sha256', $authenticationToken->getToken());

            if (hash_equals($authenticationToken->getHashedToken(), $hashedTokenFromCookie)) {
                if ($authenticationToken->getExpires() > time()) {
                    try {
                        $encryptionKey = Key::loadFromAsciiSafeString($authenticationToken->getToken());

                        $password = Crypto::decrypt($authenticationToken->getEncryptedPassword(), $encryptionKey);

                        $this->signInUser($authenticationToken->getUser(), $password);
                        return;
                    } catch (\Exception $e) {
                        die('Encryption error error for "remind me" cookie. Clear cookies to login.');
                    }
                }
            }
        }

        $this->clearRememberMeCookie($authenticationToken);
    }

    private function clearRememberMeCookie(AuthenticationToken $token = null)
    {
        if ($token) {
            $this->userRepository->deleteAuthTokenForUser($token);
        }

        setcookie(self::COOKIE_REMEMBER, null, -1, '/');
    }

    /**
     * @return AuthenticationToken
     */
    private function getAuthenticationTokenFromCookie()
    {
        $cookieValue = $this->request->getCookieParams()[self::COOKIE_REMEMBER] ?? null;
        if (isset($cookieValue)) {
            list($selector,$token) = explode(':', $cookieValue);

            $authenticationToken = $this->userRepository->getAuthenticationTokenBySelector($selector);

            if ($authenticationToken) {
                $authenticationToken->setToken($token);
                return $authenticationToken;
            }
        }
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

    /**
     * Checks if there's a user in the session, or if an authentication cookie exists
     */
    public function loadUserFromSession()
    {
        if (isset($_SESSION[self::SESSION_USER_ID])) {
            $user = $this->userRepository->getUserById($_SESSION[self::SESSION_USER_ID]);
            if ($user->isset()) {
                $user->setKey($_SESSION[self::SESSION_USER_KEY]);
                self::$signedInUser = $user;
            }
        }
        else if (isset($this->request->getCookieParams()[self::COOKIE_REMEMBER]) && $this->isRememberMeActivated) {
            $this->authenticateUsingCookie();
        }
    }

    /**
     * Loads a user to the session after signing in though username/password or authentication cookie
     * @param User $user
     * @param string $password
     * @return bool
     */
    private function signInUser(User $user, string $password)
    {
        $protectedKey = $this->userRepository->getProtectedEncryptionKeyForUser($user->getId());

        if ($protectedKey) {
            try {
                $protectedKeyObject = KeyProtectedByPassword::loadFromAsciiSafeString($protectedKey);
                $keyObject = $protectedKeyObject->unlockKey($password);
                $keyAscii = $keyObject->saveToAsciiSafeString();
                $user->setKey($keyAscii);

                $_SESSION[self::SESSION_USER_ID] = $user->getId();
                $_SESSION[self::SESSION_USER_KEY] = $user->getKey();
                session_regenerate_id();

                self::$signedInUser = $user;

                return true;
            }
            catch (BadFormatException $e) { }
            catch (EnvironmentIsBrokenException $e) { }
            catch (WrongKeyOrModifiedCiphertextException $e) { }
        }

        return false;
    }
}