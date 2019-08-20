<?php

use lib\Config;
use lib\Database;
use lib\Repositories\EncryptionKeyRepository;
use lib\Repositories\FileRepository;
use lib\Repositories\UserRepository;
use lib\Services\AuthenticationService;
use lib\Services\EncryptionService;
use lib\Services\SearchService;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


/**
 * Singletons setup
 */

$configurationFile = __DIR__ . '/../config/config.ini';

if (!is_file($configurationFile)) {
    $configurationFile .= '.template';
}

Config::load($configurationFile);
\lib\Translation::loadLanguage(Config::getInstance()->language);
\lib\Sort::loadFromSession();

$databaseConfiguration = Config::getInstance()->getDatabaseConfiguration();
$database = new Database($databaseConfiguration);

$userRepository = new UserRepository($database);
$request = \GuzzleHttp\Psr7\ServerRequest::fromGlobals();

$authenticationService = new AuthenticationService($userRepository, Config::getInstance());
$authenticationService->load($request);


/**
 * Dependency registration
 */

$containerBuilder = new ContainerBuilder();

$containerBuilder->set(Config::class, Config::getInstance());
$containerBuilder->set(Database::class, $database);
$containerBuilder->set(AuthenticationService::class, $authenticationService);
$containerBuilder->set(ServerRequestInterface::class, $request);

$containerBuilder->register(UserRepository::class, UserRepository::class)
    ->addArgument(new Reference(Database::class));

$containerBuilder->register(EncryptionService::class, EncryptionService::class);

$containerBuilder->register(EncryptionKeyRepository::class, EncryptionKeyRepository::class)
    ->addArgument(new Reference(EncryptionService::class))
    ->addArgument(new Reference(Database::class))
    ->addArgument(new Reference(AuthenticationService::class));

$containerBuilder->register(FileRepository::class, FileRepository::class)
    ->addArgument(new Reference(Database::class))
    ->addArgument(new Reference(UserRepository::class))
    ->addArgument(new Reference(EncryptionService::class))
    ->addArgument(new Reference(EncryptionKeyRepository::class));

$containerBuilder->register(SearchService::class, SearchService::class)
    ->addArgument(new Reference(FileRepository::class));

return $containerBuilder;