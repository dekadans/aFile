<?php
namespace lib\HTTP;


use function GuzzleHttp\Psr7\stream_for;
use Psr\Http\Message\ResponseInterface;

class Response
{
    protected $body;
    protected $headers = [];
    protected $statusCode;

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
     * @return ResponseInterface
     */
    public function psr7()
    {
        $response = new \GuzzleHttp\Psr7\Response();

        $body = stream_for($this->body);

        $response = $response
            ->withStatus($this->statusCode)
            ->withBody($body);

        foreach ($this->headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        return $response;
    }

    public function disableCache()
    {
        $this->addHeader('Cache-Control', 'no-cache, must-revalidate');
    }

    /**
     * @param string $header
     */
    public function addHeader(string $header, string $value)
    {
        $this->headers[$header] = $value;
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