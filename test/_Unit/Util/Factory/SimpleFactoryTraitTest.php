<?php

namespace Kraken\_Unit\Util\Factory;

use Kraken\_Unit\Util\Factory\_Mock\SimpleFactoryMock;
use Kraken\_Unit\Util\Factory\_Partial\SimpleFactoryPartial;
use Kraken\Test\TUnit;
use Kraken\Util\Factory\SimpleFactoryInterface;

class SimpleFactoryTraitTest extends TUnit
{
    use SimpleFactoryPartial;

    /**
     * @return SimpleFactoryInterface
     */
    public function createFactory()
    {
        return new SimpleFactoryMock();
    }
}
