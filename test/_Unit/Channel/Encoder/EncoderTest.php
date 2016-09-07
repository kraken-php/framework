<?php

namespace Kraken\_Unit\Channel\Encoder;

use Kraken\Channel\Encoder\Encoder;
use Kraken\Channel\Protocol\Protocol;
use Kraken\Channel\Protocol\ProtocolInterface;
use Kraken\Test\TUnit;
use Kraken\Util\Parser\ParserInterface;

class EncoderTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createEncoder();
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $encoder = $this->createEncoder();
        unset($encoder);
    }

    /**
     *
     */
    public function testApiWith_SetsProtocol()
    {
        $encoder = $this->createEncoder();

        $protocol = $this->getProtectedProperty($encoder, 'protocol');
        $this->assertSame(null, $protocol);

        $encoder->with($new = $this->createProtocol());

        $protocol = $this->getProtectedProperty($encoder, 'protocol');
        $this->assertSame($new, $protocol);
    }

    /**
     *
     */
    public function testApiEncode_EncodesProtocolUsingPassedParser()
    {
        $encoder = $this->createEncoder();
        $protocol = $this->createProtocol();

        $result = $encoder->with($protocol)->encode();

        $this->assertSame($this->encode($protocol), $result);
    }

    /**
     *
     */
    public function testApiDecode_DecodesStringUsingPassedParser()
    {
        $encoder = $this->createEncoder();
        $protocol = $this->createProtocol();

        $result = $encoder->with($protocol)->decode($str = '{}');

        $this->assertInstanceOf(ProtocolInterface::class, $result);
    }

    /**
     * @return Protocol
     */
    public function createProtocol()
    {
        return new Protocol();
    }

    /**
     * @return Encoder
     */
    public function createEncoder()
    {
        $parser = $this->getMock(ParserInterface::class);
        $parser
            ->expects($this->any())
            ->method('encode')
            ->will($this->returnCallback(function($mixed) {
                return $this->encode($mixed);
            }));
        $parser
            ->expects($this->any())
            ->method('decode')
            ->will($this->returnCallback(function($str) {
                return $this->decode($str);
            }));

        return new Encoder($parser);
    }

    /**
     * @param mixed $mixed
     * @return string
     */
    public function encode($mixed)
    {
        return "encoded";
    }

    /**
     * @param string $str
     * @return mixed
     */
    public function decode($str)
    {
        return "decoded";
    }
}
