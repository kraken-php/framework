<?php

namespace Kraken\Core\Provider\Log;

use Kraken\Config\ConfigInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Log\Handler\HandlerInterface;
use Kraken\Log\Logger;
use Kraken\Log\LoggerFactory;
use Kraken\Support\StringSupport;

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
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $config  = $core->make('Kraken\Config\ConfigInterface');

        $factory = new LoggerFactory();
        $logger  = new Logger(
            'kraken',
            [
                $this->createHandler($core, $config, 'debug', Logger::DEBUG),
                $this->createHandler($core, $config, 'info', Logger::INFO),
                $this->createHandler($core, $config, 'notice', Logger::NOTICE),
                $this->createHandler($core, $config, 'warning', Logger::WARNING),
                $this->createHandler($core, $config, 'error', Logger::EMERGENCY)
            ]
        );

        $core->instance(
            'Kraken\Log\LoggerFactory',
            $factory
        );

        $core->instance(
            'Kraken\Log\LoggerInterface',
            $logger
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Log\LoggerInterface'
        );

        $core->remove(
            'Kraken\Log\LoggerFactory'
        );
    }

    /**
     * @param CoreInterface $core
     * @param ConfigInterface $config
     * @param string $level
     * @param int $loggerLevel
     * @return HandlerInterface
     */
    private function createHandler(CoreInterface $core, ConfigInterface $config, $level, $loggerLevel)
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
