<?php

namespace lib\DataTypes;

use Psr\Http\Message\ServerRequestInterface;

class AuthenticationCookie
{
    const COOKIE_NAME = 'afile_auth';

    /** @var string */
    private $selector;
    /** @var string */
    private $token;
    /** @var string */
    private $encryptionKey;

    private function __construct(string $selector, string $token, string $encryptionKey)
    {
        $this->selector = $selector;
        $this->token = $token;
        $this->encryptionKey = $encryptionKey;
    }

    public static function createFromRequest(ServerRequestInterface $request)
    {
        $cookies = $request->getCookieParams();
        $authCookie = $cookies[self::COOKIE_NAME] ?? null;

        if (!is_null($authCookie)) {
            list($selector, $token, $key) = explode(':', $authCookie);
            return new self($selector, $token, $key);
        } else {
            return false;
        }
    }

    public function set(int $expires)
    {
        $cookie = $this->selector . ':' . $this->token . ':' . $this->encryptionKey;
        setcookie(self::COOKIE_NAME, $cookie, $expires, '/');
    }

    public function clear()
    {
        setcookie(self::COOKIE_NAME, null, -1, '/');
    }

    /**
     * @return string
     */
    public function getSelector(): string
    {
        return $this->selector;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getEncryptionKey(): string
    {
        return $this->encryptionKey;
    }
}