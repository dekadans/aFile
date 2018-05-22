<?php
namespace lib\HTTP;

class JsonResponse extends Response
{
    public function __construct(array $json, int $statusCode = 200)
    {
        $body = json_encode($json);
        $this->addHeader('Content-Type', 'application/json; charset=UTF-8');
        $this->disableCache();
        parent::__construct($body, $statusCode);
    }
}