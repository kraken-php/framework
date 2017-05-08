<?php

namespace Kraken\Root\Provider;

use Kraken\Config\ConfigInterface;
use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
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
        $config   = $container->make('Kraken\Config\ConfigInterface');
        $handlers = [];

        if ($config->exists('log.levels'))
        {
            $levels = (array) $config->get('log.levels');
        }
        else
        {
            $levels = [];
        }

        foreach ($levels as $level)
        {
            $handlers[] = $this->createHandler($config, strtolower($level), constant("\\Kraken\\Log\\Logger::$level"));
        }

        $factory = new LoggerFactory();
        $logger  = new Logger('Kraken', $handlers);

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
            'LineFormatter', [ $config->get('log.config.messagePattern'), $config->get('log.config.datePattern'), true ]
        );

        $filePermission = $config->get('log.config.filePermission');
        $fileLocking = (bool) $config->get('log.config.fileLocking');
        $filePath = $config->get('log.config.filePattern');

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
