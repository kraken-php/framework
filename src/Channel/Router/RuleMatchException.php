<?php

namespace Kraken\Channel\Router;

use Kraken\Channel\ChannelProtocolInterface;
use Kraken\Support\StringSupport;

class RuleMatchException
{
    /**
     * @var string
     */
    protected $exception;

    /**
     * @param string $exception
     */
    public function __construct($exception)
    {
        $this->exception = $exception;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->exception);
    }

    /**
     * @param string $exception
     * @param ChannelProtocolInterface $protocol
     * @return bool
     */
    public function __invoke($exception, ChannelProtocolInterface $protocol)
    {
        return StringSupport::match($this->exception, $protocol->getException());
    }
}
