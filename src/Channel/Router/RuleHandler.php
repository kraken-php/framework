<?php

namespace Kraken\Channel\Router;

use Kraken\Channel\ChannelProtocolInterface;

class RuleHandler
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * @param callable $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->callback);
    }

    /**
     * @param string $alias
     * @param ChannelProtocolInterface $protocol
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     */
    public function __invoke($alias, ChannelProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
    {
        $callback = $this->callback;

        return $callback([
            'alias'    => $alias,
            'protocol' => $protocol,
            'flags'    => $flags,
            'success'  => $success,
            'failure'  => $failure,
            'cancel'   => $cancel,
            'timeout'  => $timeout
        ]);
    }
}
