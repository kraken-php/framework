<?php

namespace Kraken\_Unit\Util\Support;

use Kraken\_Unit\Util\Support\_Mock\HashSupportMock;
use Kraken\Util\Support\HashSupport;
use Kraken\Test\TUnit;

class HashSupportTest extends TUnit
{
    /**
     *
     */
    public function testApiGenId_GeneratesUniqId()
    {
        $gen = $this->createHashSupportMock();
        $prefix = 'test';

        $this->assertRegExp("#^$prefix([a-z0-9]*?)\.([a-z0-9]*?)$#si", $gen::hash($prefix));
    }

    /**
     * @return HashSupport
     */
    public function createHashSupportMock()
    {
        return new HashSupportMock();
    }
}
