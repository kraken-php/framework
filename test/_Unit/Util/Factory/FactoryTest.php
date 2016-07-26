<?php

namespace Kraken\_Unit\Util\Factory;

use Kraken\_Unit\Util\Factory\_Partial\FactoryPartial;
use Kraken\_Unit\TestCase;
use Kraken\Util\Factory\Factory;
use Kraken\Util\Factory\FactoryInterface;

class FactoryTest extends TestCase
{
    use FactoryPartial;

    /**
     * @return FactoryInterface
     */
    public function createFactory()
    {
        return new Factory();
    }
}
