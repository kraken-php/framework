<?php

namespace Kraken\Channel\Router;

use Kraken\Channel\Protocol\ProtocolInterface;

class RouterRule
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var callable
     */
    protected $matcher;

    /**
     * @var callable
     */
    protected $handler;

    /**
     * @var bool
     */
    protected $propagate;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var array|null
     */
    protected $pointer;

    /**
     * @var bool
     */
    protected $cancelled;

    /**
     * @param RouterInterface $router
     * @param callable $matcher
     * @param callable $handler
     * @param bool $propagate
     * @param int $limit
     */
    public function __construct(RouterInterface $router, callable $matcher, callable $handler, $propagate = false, $limit = 0)
    {
        $this->router = $router;
        $this->matcher = $matcher;
        $this->handler = $handler;
        $this->propagate = $propagate;
        $this->limit = $limit;
        $this->pointer = null;
        $this->cancelled = false;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->router);
        unset($this->matcher);
        unset($this->handler);
        unset($this->propagate);
        unset($this->pointer);
        unset($this->cancelled);
    }

    /**
     * Return Router to which handler is attached to.
     *
     * @return RouterInterface
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * Match given message protocol.
     *
     * @param string $sender
     * @param ProtocolInterface $protocol
     * @return bool
     */
    public function match($sender, ProtocolInterface $protocol)
    {
        $callback = $this->matcher;
        return $callback($sender, $protocol);
    }

    /**
     * Handle given message protocol.
     *
     * @param string $sender
     * @param ProtocolInterface $protocol
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     * @return bool
     */
    public function handle($sender, ProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        $callback = $this->handler;
        $callback($sender, $protocol, $flags, $success, $failure, $cancel, $timeout);

        if ($this->limit > 0)
        {
            $this->limit--;
            if ($this->limit === 0)
            {
                $this->cancel();
            }
        }

        return $this->propagate;
    }

    /**
     * Remove this handler.
     */
    public function cancel()
    {
        if (isset($this->pointer) && isset($this->router) && !$this->cancelled)
        {
            $this->router->removeHandler($this->pointer[0], $this->pointer[1]);
            $this->cancelled = true;
        }
    }

    /**
     * @internal
     * @param string $stack
     * @param int $pointer
     */
    public function setPointer($stack, $pointer)
    {
        $this->pointer = [ $stack, $pointer ];
    }
}
