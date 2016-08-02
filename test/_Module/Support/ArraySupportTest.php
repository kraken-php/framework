<?php

namespace Kraken\_Module\Support;

use Kraken\_Unit\Support\_Mock\ArraySupportMock;
use Kraken\Support\ArraySupport;
use Kraken\Test\TModule;
use StdClass;

/**
 * @runTestsInSeparateProcesses
 */
class ArraySupportTest extends TModule
{
    /**
     *
     */
    public function testCaseArraySupport_SupportsArrayOperations()
    {
        $support = $this->createArraySupportMock();

        $array1 = [];
        $support::set($array1, 'a', $s1 = new StdClass);
        $support::set($array1, 'b.a', null);
        $support::set($array1, 'b.b', 'XYZ');

        $array2 = [];
        $support::set($array2, 'b.c', 5);
        $support::set($array2, 'b.b', null);
        $support::set($array2, 'd', $s2 = new StdClass);
        $support::remove($array2, 'b.c');

        $array = $support::merge([ $array1, $array2 ]);

        $this->assertSame(
            [
                'a'   => $s1,
                'b.a' => null,
                'b.b' => null,
                'd'   => $s2
            ],
            $array = $support::flatten($array)
        );

        $this->assertSame(
            [
                'a'   => $s1,
                'b'   => [ 'a' => null, 'b' => null ],
                'd'   => $s2
            ],
            $array = $support::expand($array)
        );

        unset($support);
        unset($array1);
        unset($array2);
    }

    /**
     * @return ArraySupport
     */
    public function createArraySupportMock()
    {
        return new ArraySupportMock();
    }
}
