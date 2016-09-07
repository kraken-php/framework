<?php

namespace Kraken\Channel\Router\RuleHandle;

use Kraken\Channel\Protocol\ProtocolInterface;

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
     * @param ProtocolInterface $protocol
     * @param int $flags
     * @param callable|null $success
     * @param callable|null $failure
     * @param callable|null $cancel
     * @param float $timeout
     */
    public function __invoke($alias, ProtocolInterface $protocol, $flags, callable $success = null, callable $failure = null, callable $cancel = null, $timeout = 0.0)
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
