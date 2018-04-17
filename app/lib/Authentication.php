<?php
namespace lib;


use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use Defuse\Crypto\KeyProtectedByPassword;
use lib\Repositories\UserRepository;

class Authentication
{
    const SESSION_USER_ID = 'aFile_User';
    const SESSION_USER_KEY = 'aFile_User_Key';
    const COOKIE_REMEMBER = 'afile_rememberme';

    /** @var User */
    private $user = null;
    /** @var UserRepository */
    private $userRepository;
    /** @var bool */
    private $isRememberMeActivated;

    public function __construct(UserRepository $userRepository, bool $isRememberMeActivated)
    {
        $this->userRepository = $userRepository;
        $this->isRememberMeActivated = $isRememberMeActivated;

        $this->checkIfAuthenticated();
    }

    public function authenticate(string $username, string $password)
    {
        $user = $this->userRepository->getUserByUsername($username);

        if ($user->isset()) {
            if (password_verify($password, $user->getHashedPassword())) {
                return $this->loadUser($user, $password);
            }
        }

        return false;
    }

    public function logout()
    {
        unset($_SESSION[self::SESSION_USER_ID]);
        unset($_SESSION[self::SESSION_USER_KEY]);
        session_regenerate_id();

        if (isset($_COOKIE[self::COOKIE_REMEMBER])) {
            $this->userRepository->deleteAuthTokenForUser($this->user->getId(), $this->getSelectorFromAuthCookie());

            unset($_COOKIE[self::COOKIE_REMEMBER]);
            setcookie(self::COOKIE_REMEMBER, null, -1, '/');
        }

        $this->user = null;
    }

    public function rememberMe(string $password)
    {
        if (!is_null($this->user) && $this->isRememberMeActivated) {
            try {
                $encryptionKey = Key::createNewRandomKey();
                $token = $encryptionKey->saveToAsciiSafeString();
                $encryptedPassword = Crypto::encrypt($password, $encryptionKey);

                $selector = bin2hex(random_bytes(6));

                $hashedToken = hash('sha256', $token);
                $expires = strtotime('+ 1 YEAR');

                $result = $this->userRepository->addAuthTokenToUser($this->user->getId(), $selector, $hashedToken, $encryptedPassword, date('Y-m-d H:i:s', $expires));

                if ($result) {
                    setcookie(self::COOKIE_REMEMBER, $selector . ':' . $token, $expires, '/');
                }
            } catch (EnvironmentIsBrokenException $e) {
            }
        }
    }

    private function getSelectorFromAuthCookie()
    {
        if (isset($_COOKIE[self::COOKIE_REMEMBER])) {
            list($selector,$token) = explode(':', $_COOKIE[self::COOKIE_REMEMBER]);
            return $selector;
        }
    }

    private function getTokenFromAuthCookie()
    {
        if (isset($_COOKIE[self::COOKIE_REMEMBER])) {
            list($selector,$token) = explode(':', $_COOKIE[self::COOKIE_REMEMBER]);
            return $token;
        }
    }

    /**
     * @return bool
     */
    public function isSignedIn() : bool
    {
        return !is_null($this->user);
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    private function checkIfAuthenticated()
    {
        if (isset($_SESSION[self::SESSION_USER_ID])) {
            $user = $this->userRepository->getUserById($_SESSION[self::SESSION_USER_ID]);
            if ($user->isset()) {
                $user->setKey($_SESSION[self::SESSION_USER_KEY]);
                $this->user = $user;
            }
        }
        else if (isset($_COOKIE[self::COOKIE_REMEMBER]) && $this->isRememberMeActivated) {
            $tokenInfoFromDb = $this->userRepository->getAuthRowBySelector($this->getSelectorFromAuthCookie());
            $token = $this->getTokenFromAuthCookie();
            $hashedTokenFromCookie = hash('sha256', $token);

            if ($tokenInfoFromDb && hash_equals($tokenInfoFromDb['hashed_token'], $hashedTokenFromCookie)) {
                if (strtotime($tokenInfoFromDb['expires']) > time()) {
                    $user = $this->userRepository->getUserById($tokenInfoFromDb['user_id']);

                    try {
                        $encryptionKey = Key::loadFromAsciiSafeString($token);

                        $password = Crypto::decrypt($tokenInfoFromDb['encrypted_password'], $encryptionKey);

                        $this->loadUser($user, $password);
                    } catch (\Exception $e) {
                        die('Encryption error error for "remind me" cookie. Clear cookies to login.');
                    }
                }
            }
        }
    }

    private function loadUser(User $user, string $password)
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

                $this->user = $user;

                return true;
            }
            catch (BadFormatException $e) { }
            catch (EnvironmentIsBrokenException $e) { }
            catch (WrongKeyOrModifiedCiphertextException $e) { }
        }

        return false;
    }
}