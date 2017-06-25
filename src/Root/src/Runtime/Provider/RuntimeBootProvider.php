<?php

namespace Kraken\Root\Runtime\Provider;

use Kraken\Container\ContainerInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;

class RuntimeBootProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Dazzle\Loop\LoopExtendedInterface',
        'Kraken\Runtime\RuntimeContainerInterface',
        'Kraken\Runtime\Supervision\SupervisorBaseInterface',
        'Kraken\Runtime\RuntimeManagerInterface'
    ];

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $loop    = $container->make('Dazzle\Loop\LoopExtendedInterface');
        $runtime = $container->make('Kraken\Runtime\RuntimeContainerInterface');
        $error   = $container->make('Kraken\Runtime\Supervision\SupervisorBaseInterface');
        $manager = $container->make('Kraken\Runtime\RuntimeManagerInterface');

        $model = $runtime->getModel();
        $model->setLoop($loop);
        $model->setSupervisor($error);
        $model->setRuntimeManager($manager);
    }
}
