<?php

namespace Kraken\Runtime\Provider\Runtime;

use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Runtime\RuntimeContainerInterface;

class RuntimeProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Core\CoreInputContextInterface',
        'Kraken\Runtime\RuntimeContainerInterface'
    ];

    /**
     * @var RuntimeContainerInterface
     */
    protected $runtime;

    /**
     * @param RuntimeContainerInterface $runtime
     */
    public function __construct(RuntimeContainerInterface $runtime)
    {
        $this->runtime = $runtime;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->runtime);
    }

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $core->instance(
            'Kraken\Core\CoreInputContextInterface',
            $this->runtime
        );

        $core->instance(
            'Kraken\Runtime\RuntimeContainerInterface',
            $this->runtime
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Core\CoreInputContextInterface'
        );

        $core->remove(
            'Kraken\Runtime\RuntimeContainerInterface'
        );
    }
}
