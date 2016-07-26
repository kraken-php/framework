<?php

namespace Kraken\_Unit\Util\Factory;

use Kraken\_Unit\Util\Factory\_Partial\SimpleFactoryPartial;
use Kraken\_Unit\TestCase;
use Kraken\Util\Factory\SimpleFactory;
use Kraken\Util\Factory\SimpleFactoryInterface;

class SimpleFactoryTest extends TestCase
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
