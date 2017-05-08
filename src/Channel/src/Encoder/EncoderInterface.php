<?php

namespace Kraken\Channel\Encoder;

use Kraken\Channel\Protocol\ProtocolInterface;

interface EncoderInterface
{
    /**
     * Set protocol to be encoded or data decoded into.
     *
     * @param ProtocolInterface $protocol
     * @return EncoderInterface
     */
    public function with(ProtocolInterface $protocol);

    /**
     * Encode protocol to string.
     *
     * @return string
     */
    public function encode();

    /**
     * Decode protocol from string.
     *
     * @param $str
     * @return ProtocolInterface
     */
    public function decode($str);
}
