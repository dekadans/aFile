<?php
namespace lib\DataTypes;

class AuthenticationToken
{
    /** @var User */
    private $user;
    /** @var string */
    private $selector;
    /** @var string */
    private $hashedToken;
    /** @var string */
    private $expires;
    /** @var string */
    private $token;

    public function __construct(User $user, string $selector, string $hashedToken, int $expires)
    {
        $this->user = $user;
        $this->selector = $selector;
        $this->hashedToken = $hashedToken;
        $this->expires = $expires;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
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
    public function getHashedToken(): string
    {
        return $this->hashedToken;
    }

    /**
     * @return int
     */
    public function getExpires(): int
    {
        return $this->expires;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param string $selector
     */
    public function setSelector(string $selector)
    {
        $this->selector = $selector;
    }

    /**
     * @param string $hashedToken
     */
    public function setHashedToken(string $hashedToken)
    {
        $this->hashedToken = $hashedToken;
    }

    /**
     * @param int $expires
     */
    public function setExpires(int $expires)
    {
        $this->expires = $expires;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
}