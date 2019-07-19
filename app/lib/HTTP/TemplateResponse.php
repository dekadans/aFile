<?php

namespace lib\HTTP;

class TemplateResponse
{
    private $htmlResponse;

    public function __construct(string $template, string $partial, string $title, array $parameters, int $statusCode = 200)
    {
        $partial = new HTMLResponse($partial, $parameters, $statusCode);
        $this->htmlResponse = new HTMLResponse($template, [
            'title' => $title,
            'partial' => $partial->getBody()
        ], $statusCode);
    }

    public function psr7()
    {
        return $this->htmlResponse->psr7();
    }
}