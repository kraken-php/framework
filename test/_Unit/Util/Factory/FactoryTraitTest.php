<?php

namespace Kraken\_Unit\Util\Factory;

use Kraken\_Unit\Util\Factory\_Mock\FactoryMock;
use Kraken\_Unit\Util\Factory\_Partial\FactoryPartial;
use Kraken\Test\TUnit;
use Kraken\Util\Factory\FactoryInterface;

class FactoryTraitTest extends TUnit
{
    use FactoryPartial;

    /**
     * @return FactoryInterface
     */
    public function createFactory()
    {
        return new FactoryMock();
    }
}
