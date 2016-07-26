<?php

namespace Kraken\_Unit\Util\Enum\_Mock;

use Kraken\Util\Enum\EnumInterface;
use Kraken\Util\Enum\EnumTrait;

class EnumMock implements EnumInterface
{
    use EnumTrait;

    /**
     * @var string
     */
    const TEST_A = 'TEST_A';

    /**
     * @var string
     */
    const TEST_B = 'TEST_B';
}
