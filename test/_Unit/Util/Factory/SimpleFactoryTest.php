<?php

namespace Kraken\_Unit\Util\Factory;

use Kraken\_Unit\Util\Factory\_Partial\SimpleFactoryPartial;
use Kraken\Test\TUnit;
use Kraken\Util\Factory\SimpleFactory;
use Kraken\Util\Factory\SimpleFactoryInterface;

class SimpleFactoryTest extends TUnit
{
    use SimpleFactoryPartial;

    /**
     * @return SimpleFactoryInterface
     */
    public function createFactory()
    {
        return new SimpleFactory();
    }
}
