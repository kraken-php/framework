<?php

namespace Kraken\_Unit\Support;

use Kraken\_Unit\Support\_Mock\ArraySupportMock;
use Kraken\_Unit\TestCase;
use Kraken\Support\ArraySupport;
use StdClass;

class ArraySupportTest extends TestCase
{
    /**
     *
     */
    public function testApiIsEmpty_ReturnsTrue_ForEmptyArray()
    {
        $support = $this->createArraySupportMock();

        $this->assertTrue($support::isEmpty([]));
    }

    /**
     *
     */
    public function testApiIsEmpty_ReturnsFalse_ForNonEmptyArray()
    {
        $support = $this->createArraySupportMock();

        $this->assertFalse($support::isEmpty([ null ]));
    }

    /**
     *
     */
    public function testApiExists_ReturnsTrue_ForSimpleKey_WhichExists()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $this->assertTrue($support::exists($array, 'a'));
    }

    /**
     *
     */
    public function testApiExists_ReturnsTrue_ForComplexKey_WhichExistsIndirectly()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $this->assertTrue($support::exists($array, 'b.b.a'));
    }

    /**
     *
     */
    public function testApiExists_ReturnsTrue_ForComplexKey_WhichExistsDirectly()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $this->assertTrue($support::exists($array, 'e.b'));
    }

    /**
     *
     */
    public function testApiExists_ReturnsFalse_ForSimpleKey_WhichDoesNotExist()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $this->assertFalse($support::exists($array, 'x'));
    }

    /**
     *
     */
    public function testApiExists_ReturnsFalse_ForComplexKey_WhichDoesNotExist()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $this->assertFalse($support::exists($array, 'x.a.c'));
    }

    /**
     *
     */
    public function testApiGet_ReturnsElement_ForExistingElement()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $this->assertSame($array['d']['a'], $support::get($array, 'd.a'));
    }

    /**
     *
     */
    public function testApiGet_ReturnsNull_ForNonExistingElement()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $this->assertEquals(null, $support::get($array, 'x.a.c'));
    }

    /**
     *
     */
    public function testApiGet_ReturnsDefault_ForNonExistingElement()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();
        $std = new StdClass;

        $this->assertSame($std, $support::get($array, 'x.a.c', $std));
    }

    /**
     *
     */
    public function testApiGet_ReturnsArray_ForEmptyStringKey()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $this->assertSame($array, $support::get($array, ''));
    }

    /**
     *
     */
    public function testApiGet_ReturnsArray_ForNullKey()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $this->assertSame($array, $support::get($array, null));
    }

    /**
     *
     */
    public function testApiSet_SetsValue_ForNonExistingKey()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();
        $std = new StdClass;

        $support::set($array, 'f.a', $std);

        $this->assertSame($std, $support::get($array, 'f.a'));
        $this->assertSame($std, $array['f']['a']);
    }

    /**
     *
     */
    public function testApiSet_SetsValue_ForExistingKey()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();
        $std = new StdClass;

        $support::set($array, 'b.a', $std);

        $this->assertSame($std, $support::get($array, 'b.a'));
        $this->assertSame($std, $array['b']['a']);
    }

    /**
     *
     */
    public function testApiSet_ReplacesArray_ForEmptyStringKey()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();
        $std = new StdClass;

        $support::set($array, '', $std);

        $this->assertSame($std, $array);
    }

    /**
     *
     */
    public function testApiSet_ReplacesArray_ForNullKey()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();
        $std = new StdClass;

        $support::set($array, null, $std);

        $this->assertSame($std, $array);
    }

    /**
     *
     */
    public function testApiSet_ReturnsSameArray()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();
        $std = new StdClass;

        $new = $support::set($array, 'x.a.c', $std);

        $this->assertSame($array, $new);
    }

    /**
     *
     */
    public function testApiRemove_RemovesElement_ForExistingKey()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $this->assertTrue(isset($array['b']['a']));

        $support::remove($array, 'b.a');

        $this->assertFalse(isset($array['b']['a']));
    }

    /**
     *
     */
    public function testApiRemove_DoesNothing_ForNonExistingKey()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $this->assertFalse(isset($array['f']['a']));

        $support::remove($array, 'f.a');
    }

    /**
     *
     */
    public function testApiRemove_EmptiesArray_ForEmptyStringKey()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $support::remove($array, '');

        $this->assertSame([], $array);
    }

    /**
     *
     */
    public function testApiRemove_EmptiesArray_ForNullKey()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $support::remove($array, null);

        $this->assertSame([], $array);
    }

    /**
     *
     */
    public function testApiFlatten_FlattensArray()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $this->assertSame(
            $this->getFlattenedArray($array),
            $support::flatten($array)
        );
    }

    /**
     *
     */
    public function testApiExpand_ExpandsArray()
    {
        $support = $this->createArraySupportMock();
        $array = $this->getArray();

        $this->assertSame(
            $support::expand($array),
            $support::expand($this->getFlattenedArray($array))
        );
    }

    /**
     *
     */
    public function testApiMerge_ReturnsEmptyArray_ForEmptyArray()
    {
        $support = $this->createArraySupportMock();

        $this->assertSame([], $support::merge([]));
    }

    /**
     *
     */
    public function testApiMerge_ReturnsSameArray_WhenOnlyOneArrayPassed()
    {
        $support = $this->createArraySupportMock();
        $array = [
            'a' => $s1 = new StdClass,
            'b' => [ 'a' => 5, 'b' => null ],
            'c' => 'XYZ'
        ];

        $this->assertSame($array, $support::merge([ $array ]));
    }

    /**
     *
     */
    public function testApiMerge_MergesMultipleArraysPreservingDotNotation()
    {
        $support = $this->createArraySupportMock();

        $array1 = [
            'a' => $s1 = new StdClass,
            'b' => [ 'a' => 5, 'b' => null ],
            'c' => 'XYZ'
        ];

        $array2 = [
            'a' => $s2 = new StdClass,
            'b' => [ 'b' => 10, 'c' => $s3 = new StdClass ],
            'd' => 0
        ];

        $expected = [
            'a' => $s2,
            'b' => [ 'a' => 5, 'b' => 10, 'c' => $s3 ],
            'c' => 'XYZ',
            'd' => 0
        ];

        $this->assertSame($expected, $support::merge([ $array1, $array2 ]));
    }

    /**
     *
     */
    public function testApiReplace_ReturnsEmptyArray_ForEmptyArray()
    {
        $support = $this->createArraySupportMock();

        $this->assertSame([], $support::replace([]));
    }

    /**
     *
     */
    public function testApiReplace_ReturnsSameArray_WhenOnlyOneArrayPassed()
    {
        $support = $this->createArraySupportMock();
        $array = [
            'a' => $s1 = new StdClass,
            'b' => [ 'a' => 5, 'b' => null ],
            'c' => 'XYZ'
        ];

        $this->assertSame($array, $support::replace([ $array ]));
    }

    /**
     *
     */
    public function testApiReplace_ReplacesMultipleArraysPreservingDotNotation()
    {
        $support = $this->createArraySupportMock();

        $array1 = [
            'a' => $s1 = new StdClass,
            'b' => [ 'a' => 5, 'b' => null ],
            'c' => 'XYZ'
        ];

        $array2 = [
            'a' => $s2 = new StdClass,
            'b' => [ 'b' => 10, 'c' => $s3 = new StdClass ],
            'd' => 0
        ];

        $expected = [
            'a' => $s2,
            'b' => [ 'b' => 10, 'c' => $s3 ],
            'c' => 'XYZ',
            'd' => 0
        ];

        $this->assertSame($expected, $support::replace([ $array1, $array2 ]));
    }

    /**
     *
     */
    public function testApiNormalizeKey_NormalizesKey()
    {
        $support = $this->createArraySupportMock();

        $before = "..\0  \tAB\0de  da\x0B.AX.";
        $after  = "ABdeda.AX";

        $this->assertSame($after, $support::normalizeKey($before));
    }

    /**
     *
     */
    public function testApiNormalizeKey_ReturnsNull_ForNullKey()
    {
        $support = $this->createArraySupportMock();

        $this->assertSame(null, $support::normalizeKey(null));
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return [
            'a' => null,
            'b' => [
                'a' => 5,
                'b' => [
                    'a' => new StdClass
                ],
                'c' => 'ABC'
            ],
            'c' => 'C',
            'd' => [
                'a' => new StdClass
            ],
            'e.a' => 0,
            'e.b' => null
        ];
    }

    /**
     * @param array $array
     * @return array
     */
    public function getFlattenedArray($array)
    {
        return [
            'a'     => $array['a'],
            'b.a'   => $array['b']['a'],
            'b.b.a' => $array['b']['b']['a'],
            'b.c'   => $array['b']['c'],
            'c'     => $array['c'],
            'd.a'   => $array['d']['a'],
            'e.a'   => $array['e.a'],
            'e.b'   => $array['e.b']
        ];
    }

    /**
     * @return ArraySupport
     */
    public function createArraySupportMock()
    {
        return new ArraySupportMock();
    }
}
