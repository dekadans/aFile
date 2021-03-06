<?php
/**
 * @var \Psr\Container\ContainerInterface $container
 */

require_once '../vendor/autoload.php';

session_start();

$container = require '../app/container.php';
require_once '../app/webinit.php';

$request = $container->get(\Psr\Http\Message\ServerRequestInterface::class);

$queryParams = $request->getQueryParams();

if (isset($queryParams['do']) && !empty($queryParams['do'])) {
    $do = '\\controllers\\' . $queryParams['do'];

    if (class_exists($do)) {
        /** @var \controllers\AbstractController $controller */
        $controller = new $do($container);

        if ($controller->checkAccess()) {
            if (method_exists($controller, 'init')) {
                $response = $controller->init();

                if ($response instanceof \Psr\Http\Message\ResponseInterface) {
                    printResponse($response);
                    die;
                }
            }

            $action = $queryParams['action'] ?? null;

            if ($action) {
                $action = 'action' . ucfirst($action);
            }
            else {
                $action = 'index';
            }

            if (method_exists($controller, $action)) {
                /** @var \lib\HTTP\Response $response */
                $response = $controller->$action();
            }
            else {
                $errorText = 'NOT_FOUND';
            }
        }
        else {
            $errorText = 'ACCESS_DENIED';
        }
    }
    else {
        $errorText = 'NOT_FOUND';
    }

    if (isset($errorText)) {
        $response = (new \lib\HTTP\JsonResponse([
            'error' => $container->get(\lib\Repositories\TranslationRepository::class)->translate($errorText)
        ]))->psr7();
    }

    printResponse($response);
    die;
}
