<?php

namespace Kraken\Channel\Router\RuleMatch;

use Kraken\Channel\Protocol\ProtocolInterface;
use Kraken\Util\Support\StringSupport;

class RuleMatchOrigin
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->name);
    }

    /**
     * @param string $name
     * @param ProtocolInterface $protocol
     * @return bool
     */
    public function __invoke($name, ProtocolInterface $protocol)
    {
        return StringSupport::match($this->name, $protocol->getOrigin());
    }
}
