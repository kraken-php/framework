<?php

namespace Kraken\Runtime\Provider\Runtime;

use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;

class RuntimeBootProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Loop\LoopExtendedInterface',
        'Kraken\Runtime\RuntimeContainerInterface',
        'Kraken\Runtime\Supervisor\SupervisorBaseInterface',
        'Kraken\Runtime\RuntimeManagerInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $loop    = $core->make('Kraken\Loop\LoopExtendedInterface');
        $runtime = $core->make('Kraken\Runtime\RuntimeContainerInterface');
        $error   = $core->make('Kraken\Runtime\Supervisor\SupervisorBaseInterface');
        $manager = $core->make('Kraken\Runtime\RuntimeManagerInterface');

        $model = $runtime->getModel();
        $model->setLoop($loop);
        $model->setSupervisor($error);
        $model->setRuntimeManager($manager);
    }
}
