<?php

namespace Kraken\_Unit\Config\Overwrite;

use Kraken\Config\Overwrite\OverwriteReverseMerger;
use Kraken\Test\TUnit;
use Kraken\Util\Support\ArraySupport;

class OverwriteReverseMergerTest extends TUnit
{
    /**
     *
     */
    public function testApiInvoke_MergesConfiguration()
    {
        $overwrite = $this->createOverwriteHandler();

        $old = $this->getRawData();
        $new = [
            'b' => [ 'c' => 'new_Option' ],
            'h' => 'test',
            'a' => 5
        ];

        $data = $overwrite($old, $new);

        $this->assertSame(
            ArraySupport::merge([ $new, $old ]),
            $data
        );
    }

    /**
     * @return array
     */
    public function getRawData()
    {
        return [
            'a' => null,
            'b' => [
                'a' => 5,
                'b' => [
                    'a' => 0002
                ],
                'c' => 'ABC'
            ],
            'c' => 'C',
            'd' => [
                'a' => 'TEST'
            ],
            'e.a' => 0,
            'e.b' => null
        ];
    }

    /**
     * @return callable
     */
    public function createOverwriteHandler()
    {
        return new OverwriteReverseMerger();
    }
}
