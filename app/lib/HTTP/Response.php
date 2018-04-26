<?php
namespace lib\HTTP;


class Response
{
    private $body;
    private $headers = [];
    private $statusCode;

    /**
     * @param string $body
     * @param int $statusCode
     */
    public function __construct(string $body, int $statusCode = 200)
    {
        $this->body = $body;
        $this->statusCode = $statusCode;
    }

    /**
     * @return string
     */
    public function output()
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $header) {
            header($header);
        }
        return $this->body;
    }

    public function disableCache()
    {
        $this->addHeader('Cache-Control: no-cache, must-revalidate');
    }

    /**
     * @param string $header
     */
    public function addHeader(string $header)
    {
        $this->headers[] = $header;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body)
    {
        $this->body = $body;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
    }
}