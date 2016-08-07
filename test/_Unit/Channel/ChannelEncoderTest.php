<?php

namespace Kraken\_Unit\Channel;

use Kraken\Channel\ChannelEncoder;
use Kraken\Channel\ChannelProtocol;
use Kraken\Channel\ChannelProtocolInterface;
use Kraken\Test\TUnit;
use Kraken\Util\Parser\ParserInterface;

class ChannelEncoderTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createChannelEncoder();
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $encoder = $this->createChannelEncoder();
        unset($encoder);
    }

    /**
     *
     */
    public function testApiWith_SetsProtocol()
    {
        $encoder = $this->createChannelEncoder();

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
        $encoder = $this->createChannelEncoder();
        $protocol = $this->createProtocol();

        $result = $encoder->with($protocol)->encode();

        $this->assertSame($this->encode($protocol), $result);
    }

    /**
     *
     */
    public function testApiDecode_DecodesStringUsingPassedParser()
    {
        $encoder = $this->createChannelEncoder();
        $protocol = $this->createProtocol();

        $result = $encoder->with($protocol)->decode($str = '{}');

        $this->assertInstanceOf(ChannelProtocolInterface::class, $result);
    }

    /**
     * @return ChannelProtocol
     */
    public function createProtocol()
    {
        return new ChannelProtocol();
    }

    /**
     * @return ChannelEncoder
     */
    public function createChannelEncoder()
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

        return new ChannelEncoder($parser);
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
