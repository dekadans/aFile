<?php

use GuzzleHttp\Psr7\ServerRequest;
use lib\Repositories\ConfigurationRepository;
use lib\Database;
use lib\Repositories\EncryptionKeyRepository;
use lib\Repositories\FileRepository;
use lib\Repositories\UserRepository;
use lib\Services\AuthenticationService;
use lib\Services\CreateFileService;
use lib\Services\EncryptionService;
use lib\Services\SearchService;
use lib\Services\SortService;
use lib\Repositories\TranslationRepository;
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

$config = new ConfigurationRepository($configurationFile);
$translationService = new TranslationRepository($config->find('language'));

$databaseConfiguration = $config->getDatabaseConfiguration();
$database = new Database($databaseConfiguration);

$userRepository = new UserRepository($database, $config);
$request = ServerRequest::fromGlobals();

$authenticationService = new AuthenticationService($userRepository, $config);
$authenticationService->load($request);


/**
 * Dependency registration
 */

$containerBuilder = new ContainerBuilder();

$containerBuilder->set(ConfigurationRepository::class, $config);
$containerBuilder->set(Database::class, $database);
$containerBuilder->set(AuthenticationService::class, $authenticationService);
$containerBuilder->set(ServerRequestInterface::class, $request);
$containerBuilder->set(SortService::class, SortService::loadFromSession());
$containerBuilder->set(TranslationRepository::class, $translationService);

$containerBuilder->register(UserRepository::class, UserRepository::class)
    ->addArgument(new Reference(Database::class))
    ->addArgument(new Reference(ConfigurationRepository::class));

$containerBuilder->register(EncryptionService::class, EncryptionService::class);

$containerBuilder->register(EncryptionKeyRepository::class, EncryptionKeyRepository::class)
    ->addArgument(new Reference(EncryptionService::class))
    ->addArgument(new Reference(Database::class))
    ->addArgument(new Reference(AuthenticationService::class));

$containerBuilder->register(FileRepository::class, FileRepository::class)
    ->addArgument(new Reference(Database::class))
    ->addArgument(new Reference(UserRepository::class))
    ->addArgument(new Reference(EncryptionService::class))
    ->addArgument(new Reference(EncryptionKeyRepository::class))
    ->addArgument(new Reference(SortService::class))
    ->addArgument(new Reference(ConfigurationRepository::class));

$containerBuilder->register(SearchService::class, SearchService::class)
    ->addArgument(new Reference(FileRepository::class))
    ->addArgument(new Reference(ConfigurationRepository::class));

$containerBuilder->register(CreateFileService::class, CreateFileService::class)
    ->addArgument(new Reference(FileRepository::class));

return $containerBuilder;