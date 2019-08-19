<?php

use lib\Database;
use lib\Repositories\EncryptionKeyRepository;
use lib\Repositories\FileRepository;
use lib\Repositories\UserRepository;
use lib\Services\AuthenticationService;
use lib\Services\EncryptionService;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


/**
 * Singletons setup
 */

\lib\Config::load(__DIR__ . '/../config/config.ini');
\lib\Translation::loadLanguage(\lib\Config::getInstance()->language);
\lib\Sort::loadFromSession();

$userRepository = new UserRepository(Database::getInstance());
$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

$authenticationService = new AuthenticationService($userRepository);
$authenticationService->load($request);


/**
 * Dependency registration
 */

$containerBuilder = new ContainerBuilder();

$containerBuilder->set(Database::class, Database::getInstance());
$containerBuilder->set(AuthenticationService::class, $authenticationService);
$containerBuilder->set(ServerRequestInterface::class, $request);

$containerBuilder->register(UserRepository::class, UserRepository::class)
    ->addArgument(new Reference(Database::class));

$containerBuilder->register(EncryptionService::class, EncryptionService::class);

$containerBuilder->register(EncryptionKeyRepository::class, EncryptionKeyRepository::class)
    ->addArgument(new Reference(EncryptionService::class))
    ->addArgument(new Reference(AuthenticationService::class));

$containerBuilder->register(FileRepository::class, FileRepository::class)
    ->addArgument(new Reference(Database::class))
    ->addArgument(new Reference(UserRepository::class))
    ->addArgument(new Reference(EncryptionService::class))
    ->addArgument(new Reference(EncryptionKeyRepository::class));

return $containerBuilder;