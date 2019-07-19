<?php

namespace lib\Services;

use lib\DataTypes\AuthenticationCookie;
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

    public function load(ServerRequestInterface $request)
    {
        if (is_null(self::$signedInUser)) {
            $cookie = AuthenticationCookie::createFromRequest($request);

            if ($cookie !== false) {
                if (isset($_SESSION[self::USER_SESSION_NAME])) {
                    $userId = $_SESSION[self::USER_SESSION_NAME];
                    $user = $this->userRepository->getUserById($userId);

                    if ($user->isset()) {
                        $user->setKey($cookie->getEncryptionKey());
                        self::$signedInUser = $user;
                        return true;
                    }
                } else {
                    // Authenticate using cookie
                    $authTokenInDb = $this->userRepository->getAuthenticationTokenBySelector($cookie->getSelector());

                }
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}