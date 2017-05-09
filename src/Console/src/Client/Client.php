<?php

namespace Kraken\Console\Client;

use Kraken\Core\CoreAwareTrait;
use Kraken\Core\CoreInterface;
use Kraken\Event\BaseEventEmitter;
use Kraken\Loop\LoopExtendedAwareTrait;

class Client extends BaseEventEmitter implements ClientInterface
{
    use CoreAwareTrait;
    use LoopExtendedAwareTrait;

    /**
     *
     */
    public function __destruct()
    {
        $this->setCore(null);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getType()
    {
        return $this->getCore()->getType();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getParent()
    {
        return null;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getAlias()
    {
        return 'Client';
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getName()
    {
        return 'Client';
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getArgs()
    {
        return [];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onStart(callable $callback)
    {
        return $this->on('start', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onStop(callable $callback)
    {
        return $this->on('stop', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function onCommand(callable $callback)
    {
        return $this->on('command', $callback);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function start()
    {
        $this->getLoop()->onTick(function() {
            $this->emit('start');
            $this->emit('command');
        });

        $this->getLoop()->start();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stop()
    {
        $this->emit('stop');
        $this->getLoop()->stop();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function config(CoreInterface $core)
    {
        return [];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function boot(CoreInterface $core)
    {
        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function construct(CoreInterface $core)
    {
        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function internalConfig(CoreInterface $core)
    {
        return $this->config($core);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function internalBoot(CoreInterface $core)
    {
        $core->instance(Client::class, $this);

        return $this->boot($core);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function internalConstruct(CoreInterface $core)
    {
        return $this->construct($core);
    }
}
