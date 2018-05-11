<?php
namespace lib\HTTP;

class HTMLResponse extends Response
{
    public function __construct(string $viewFile, array $parameters, int $statusCode = 200)
    {
        extract($parameters);
        ob_start();
        require_once __DIR__ . '/../../views/' . $viewFile . '.php';
        $content = ob_get_clean();

        parent::__construct($content, $statusCode);
    }
}