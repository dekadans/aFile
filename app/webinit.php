<?php
/**
 * @var \Psr\Container\ContainerInterface $container
 */

$port = (!in_array($_SERVER['SERVER_PORT'], [80, 443]) ? ':' . $_SERVER['SERVER_PORT'] : '');
define('AFILE_LOCATION', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . $port . preg_replace('/[a-z]*\.php[a-z0-9\/]*/', '', $_SERVER['PHP_SELF']));

/**
 * Set up basic error handling
 */
set_exception_handler(function (\Throwable $ex) use ($container) {
    $response = new \lib\HTTP\HTMLResponse('partials/exceptionError', [
        'exception' => $ex,
        'lang' => $container->get(\lib\Repositories\TranslationRepository::class),
        'config' => $container->get(\lib\Repositories\ConfigurationRepository::class)
    ], 500);
    printResponse($response->psr7());
    die;
});

if (!file_exists(__DIR__ . '/../config/config.ini')) {
    $response = new \lib\HTTP\Response('No config found. Please run installation script.', 500);
    printResponse($response->psr7());
    die;
}

function printResponse(\Psr\Http\Message\ResponseInterface $response)
{
    http_response_code($response->getStatusCode());

    foreach ($response->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header(sprintf('%s: %s', $name, $value), false);
        }
    }

    $stream = $response->getBody();

    while (!$stream->eof()) {
        echo $stream->read(100000);
    }

    $stream->close();
}
