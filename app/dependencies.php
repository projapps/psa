<?php
declare(strict_types=1);

use App\Validators\RespectValidator;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);
            $processor = new UidProcessor();
            $logger->pushProcessor($processor);
            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);
            return $logger;
        },
        PhpRenderer::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $rendererSettings = $settings['renderer'];
            $renderer = new PhpRenderer($rendererSettings['template_path']);
            return $renderer;
        },
        Messages::class => function (ContainerInterface $c) {
            return new Messages();
        },
        RespectValidator::class => function (ContainerInterface $c) {
            return new RespectValidator();
        }
    ]);
};