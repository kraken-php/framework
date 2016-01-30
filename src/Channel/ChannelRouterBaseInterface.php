<?php

namespace Kraken\Channel;

interface ChannelRouterBaseInterface
{
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
    public function handle($name, ChannelProtocolInterface $protocol, $flags = 0, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0);

    /**
     *
     */
    public function erase();

    /**
     * @param callable $matcher
     * @param callable $handler
     * @param bool $propagate
     * @param int $limit
     * @return ChannelRouterHandler|ChannelRouterHandler[]
     */
    public function addRule(callable $matcher, callable $handler, $propagate = false, $limit = 0);

    /**
     * @param callable $handler
     * @param bool $propagate
     * @param int $limit
     * @return ChannelRouterHandler|ChannelRouterHandler[]
     */
    public function addAnchor(callable $handler, $propagate = false, $limit = 0);
}
