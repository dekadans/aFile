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

    public function __construct(string $selector, string $encryptionKey)
    {
        $this->selector = $selector;
        $this->token = $encryptionKey;
    }

    public static function createFromRequest(ServerRequestInterface $request)
    {
        $cookies = $request->getCookieParams();
        $authCookie = $cookies[self::COOKIE_NAME] ?? null;

        if (!is_null($authCookie)) {
            list($selector, $key) = explode(':', $authCookie);
            return new self($selector, $key);
        } else {
            return false;
        }
    }

    public function set(int $expires)
    {
        $cookie = $this->selector . ':' . $this->token;
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
     * @param string $selector
     */
    public function setSelector(string $selector)
    {
        $this->selector = $selector;
    }
}
