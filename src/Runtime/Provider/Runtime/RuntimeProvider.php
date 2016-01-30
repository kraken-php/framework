<?php

namespace Kraken\Runtime\Provider\Runtime;

use Kraken\Core\CoreInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Runtime\RuntimeInterface;

class RuntimeProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Core\CoreInputContextInterface',
        'Kraken\Runtime\RuntimeInterface'
    ];

    /**
     * @var RuntimeInterface
     */
    protected $runtime;

    /**
     * @param RuntimeInterface $runtime
     */
    public function __construct(RuntimeInterface $runtime)
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
            'Kraken\Runtime\RuntimeInterface',
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
            'Kraken\Runtime\RuntimeInterface'
        );
    }
}
