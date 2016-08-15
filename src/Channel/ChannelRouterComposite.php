<?php

namespace Kraken\Channel;

use Kraken\Throwable\Exception\Logic\Resource\ResourceUndefinedException;

class ChannelRouterComposite implements ChannelRouterCompositeInterface
{
    /**
     * @var ChannelRouterBaseInterface[]|ChannelRouterCompositeInterface[]
     */
    protected $bus;

    /**
     * @param ChannelRouterBaseInterface[]|ChannelRouterCompositeInterface[] $bus
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
     * @param string $name
     * @return ChannelRouterBaseInterface|ChannelRouterCompositeInterface
     * @throws ResourceUndefinedException
     */
    public function bus($name)
    {
        if (!isset($this->bus[$name]))
        {
            throw new ResourceUndefinedException("Kraken\\Channel\\ChannelRouterComposite has no registered bus $name.");
        }

        return $this->bus[$name];
    }

    /**
     * @param string $name
     * @param ChannelRouterBaseInterface|ChannelRouterCompositeInterface $router
     * @return ChannelCompositeInterface
     */
    public function setBus($name, $router)
    {
        $this->bus[$name] = $router;

        return $this;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function existsBus($name)
    {
        return isset($this->bus[$name]);
    }

    /**
     * @param string $name
     * @return ChannelCompositeInterface
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
     * @return ChannelRouterBaseInterface[]|ChannelRouterCompositeInterface[]
     */
    public function getBuses()
    {
        return $this->bus;
    }

    /**
     * @param string $name
     * @param ChannelProtocolInterface $protocol
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return bool
     */
    public function handle($name, ChannelProtocolInterface $protocol, $flags = 0, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
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
     *
     */
    public function erase()
    {
        foreach ($this->bus as $name=>$router)
        {
            $router->erase();
        }
    }

    /**
     * @param callable $matcher
     * @param callable $handler
     * @param bool $propagate
     * @param int $limit
     * @return ChannelRouterHandler|ChannelRouterHandler[]
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
     * @param callable $handler
     * @param bool $propagate
     * @param int $limit
     * @return ChannelRouterHandler|ChannelRouterHandler[]
     */
    public function addAnchor(callable $handler, $propagate = false, $limit = 0)
    {
        $handlers = [];

        foreach ($this->bus as $bus=>$router)
        {
            $handlers[] = $router->addAnchor($handler, $propagate, $limit);
        }

        return $handlers;
    }
}
