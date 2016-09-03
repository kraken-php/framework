<?php

namespace Kraken\Framework\Provider;

use Kraken\Config\ConfigInterface;
use Kraken\Container\ContainerInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Log\Handler\HandlerInterface;
use Kraken\Log\Logger;
use Kraken\Log\LoggerFactory;
use Kraken\Util\Support\StringSupport;

class LogProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Config\ConfigInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Log\LoggerInterface',
        'Kraken\Log\LoggerFactory'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $config  = $container->make('Kraken\Config\ConfigInterface');

        $factory = new LoggerFactory();
        $logger  = new Logger(
            'Kraken',
            [
                $this->createHandler($config, 'error',   Logger::EMERGENCY),
                $this->createHandler($config, 'warning', Logger::WARNING),
                $this->createHandler($config, 'notice',  Logger::NOTICE),
                $this->createHandler($config, 'info',    Logger::INFO),
                $this->createHandler($config, 'debug',   Logger::DEBUG),
            ]
        );

        $container->instance(
            'Kraken\Log\LoggerFactory',
            $factory
        );

        $container->instance(
            'Kraken\Log\LoggerInterface',
            $logger
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Log\LoggerInterface'
        );

        $container->remove(
            'Kraken\Log\LoggerFactory'
        );
    }

    /**
     * @param ConfigInterface $config
     * @param string $level
     * @param int $loggerLevel
     * @return HandlerInterface
     */
    private function createHandler(ConfigInterface $config, $level, $loggerLevel)
    {
        $factory = new LoggerFactory();

        $formatter = $factory->createFormatter(
            'LineFormatter', [ $config->get('log.messagePattern'), $config->get('log.datePattern'), true ]
        );

        $filePermission = $config->get('log.filePermission');
        $fileLocking = (bool) $config->get('log.fileLocking');
        $filePath = $config->get('log.filePattern');

        $loggerHandler = $factory->createHandler(
            'StreamHandler',
            [
                $this->filePath($filePath, $level),
                $loggerLevel,
                false,
                $filePermission,
                $fileLocking
            ]
        );
        $loggerHandler
            ->setFormatter($formatter);

        return $loggerHandler;
    }

    /**
     * @param string $path
     * @param string $level
     * @return string
     */
    private function filePath($path, $level)
    {
        return StringSupport::parametrize($path, [
            'level' => $level,
            'date'  => date('Y-m-d'),
            'time'  => date('H:i:s')
        ]);
    }
}
