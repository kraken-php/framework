<?php

namespace Kraken\_Unit\Support;

use Kraken\_Unit\Support\_Mock\TimeSupportMock;
use Kraken\_Unit\TestCase;
use Kraken\Support\TimeSupport;

class TimeSupportTest extends TestCase
{
    /**
     *
     */
    public function testApiNow_ReturnsTimestampForNow()
    {
        $time = $this->createTimeSupportMock();

        $expected = time() * 1000;
        $now = $time->now();

        $this->assertGreaterThanOrEqual($expected, $now);
        $this->assertLessThan($expected + 1000, $now);
    }

    /**
     * @return TimeSupport
     */
    public function createTimeSupportMock()
    {
        return new TimeSupportMock();
    }
}
