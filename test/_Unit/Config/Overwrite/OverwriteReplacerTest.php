<?php

namespace Kraken\_Unit\Config\Overwrite;

use Kraken\Config\Overwrite\OverwriteReplacer;
use Kraken\Test\TUnit;
use Kraken\Util\Support\ArraySupport;

class OverwriteReplacerTest extends TUnit
{
    /**
     *
     */
    public function testApiInvoke_MergesConfiguration()
    {
        $overwrite = $this->createOverwriteHandler();

        $old = $this->getRawData();
        $new = [
            'b' => [ 'b' => 'new_Option' ],
            'h' => 'test',
            'a' => 5
        ];

        $data = $overwrite($old, $new);

        $this->assertSame(
            ArraySupport::replace([ $old, $new ]),
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
        return new OverwriteReplacer();
    }
}
