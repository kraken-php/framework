<?php

namespace Kraken\_Unit\Util\Enum;

use Kraken\_Unit\TestCase;
use Kraken\_Unit\Util\Enum\_Mock\EnumMock;
use Kraken\Util\Enum\EnumInterface;

class EnumTraitTest extends TestCase
{
    /**
     *
     */
    public function testApiIsSupported_ReturnsTrue_ForExistingConstant()
    {
        $enum = $this->createTraitMock();

        $this->assertTrue($enum->isSupported($enum::TEST_A));
    }

    /**
     *
     */
    public function testApiIsSupported_ReturnsFalse_ForNonExistingConstant()
    {
        $enum = $this->createTraitMock();

        $this->assertFalse($enum->isSupported('NonExisting'));
    }

    /**
     *
     */
    public function testApiGetSupported_ReturnsSupportedConsts()
    {
        $enum = $this->createTraitMock();

        $this->assertSame(
            [
                $enum::TEST_A => $enum::TEST_A,
                $enum::TEST_B => $enum::TEST_B,
            ],
            $enum->getSupported()
        );
    }

    /**
     * @return EnumInterface|EnumMock
     */
    protected function createTraitMock()
    {
        return new EnumMock();
    }
}
