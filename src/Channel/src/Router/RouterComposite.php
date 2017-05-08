<?php

namespace Kraken\Channel\Router;

use Kraken\Channel\Protocol\ProtocolInterface;
use Kraken\Throwable\Exception\Logic\ResourceUndefinedException;

class RouterComposite implements RouterCompositeInterface
{
    /**
     * @var RouterInterface[]|RouterCompositeInterface[]
     */
    protected $bus;

    /**
     * @param RouterInterface[]|RouterCompositeInterface[] $bus
     */
    public function __construct($bus = [])
    {
        $this->bus = [];

        foreach ($bus as $name=>$router)
        {
            $this->setBus($name, $router);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->bus);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsBus($name)
    {
        return isset($this->bus[$name]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getBus($name)
    {
        if (!isset($this->bus[$name]))
        {
            throw new ResourceUndefinedException(__CLASS__ . " has no registered bus $name.");
        }

        return $this->bus[$name];
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setBus($name, $router)
    {
        $this->bus[$name] = $router;

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeBus($name)
    {
        if (isset($this->bus[$name]))
        {
            unset($this->bus[$name]);
        }

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getBuses()
    {
        return $this->bus;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function handle($name, ProtocolInterface $protocol, $flags = 0, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        $handled = false;

        foreach ($this->bus as $name=>$router)
        {
            if ($router->handle($name, $protocol, $flags, $success, $failure, $cancel, $timeout))
            {
                $handled = true;
            }
        }

        return $handled;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function erase()
    {
        foreach ($this->bus as $name=>$router)
        {
            $router->erase();
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addRule(callable $matcher, callable $handler, $propagate = false, $limit = 0)
    {
        $handlers = [];

        foreach ($this->bus as $bus=>$router)
        {
            $handlers[] = $router->addRule($matcher, $handler, $propagate, $limit);
        }

        return $handlers;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function addDefault(callable $handler, $propagate = false, $limit = 0)
    {
        $handlers = [];

        foreach ($this->bus as $bus=>$router)
        {
            $handlers[] = $router->addDefault($handler, $propagate, $limit);
        }

        return $handlers;
    }
}
