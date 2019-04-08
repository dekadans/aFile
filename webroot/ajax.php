<?php
require_once '../app/init.php';

$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();
$queryParams = $request->getQueryParams();

if (isset($queryParams['do']) && !empty($queryParams['do'])) {
    $do = '\\controllers\\' . $queryParams['do'];

    if (class_exists($do)) {
        /** @var \controllers\AbstractController $controller */
        $controller = new $do($request);

        if (\lib\Acl::checkControllerAccess($controller)) {
            if (method_exists($controller, 'init')) {
                $controller->init();
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
            'error' => \lib\Translation::getInstance()->translate($errorText)
        ]))->psr7();
    }

    printResponse($response);
    die;
}
