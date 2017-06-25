<?php

namespace Kraken\Channel\Router\RuleMatch;

use Kraken\Channel\Protocol\ProtocolInterface;
use Dazzle\Util\Support\StringSupport;

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
     * @param ProtocolInterface $protocol
     * @return bool
     */
    public function __invoke($exception, ProtocolInterface $protocol)
    {
        return StringSupport::match($this->exception, $protocol->getException());
    }
}
