<?php

namespace Kraken\Root\Console\Server\Provider;

use Kraken\Console\Server\Manager\ProjectManager;
use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Util\System\SystemUnix;

class ProjectProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Config\ConfigInterface',
        'Kraken\Filesystem\FilesystemInterface',
        'Kraken\Runtime\RuntimeContainerInterface',
        'Kraken\Runtime\Service\ChannelInternal',
        'Kraken\Util\System\SystemInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Console\Server\Manager\ProjectManagerInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $config  = $container->make('Kraken\Config\ConfigInterface');
        $runtime = $container->make('Kraken\Runtime\RuntimeContainerInterface');
        $channel = $container->make('Kraken\Runtime\Service\ChannelInternal');
        $system  = $container->make('Kraken\Util\System\SystemInterface');
        $fs      = $container->make('Kraken\Filesystem\FilesystemInterface');

        $manager = new ProjectManager($runtime, $channel, $system, $fs);

        $manager->setProjectRoot($config->get('project.config.main.alias'));
        $manager->setProjectName($config->get('project.config.main.name'));

        $container->instance(
            'Kraken\Console\Server\Manager\ProjectManagerInterface',
            $manager
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        $container->remove(
            'Kraken\Console\Server\Manager\ProjectManagerInterface'
        );
    }
}
