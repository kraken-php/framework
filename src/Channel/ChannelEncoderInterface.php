<?php

namespace Kraken\Channel;

interface ChannelEncoderInterface
{
    /**
     * Set protocol to be encoded or data decoded into.
     *
     * @param ChannelProtocolInterface $protocol
     * @return ChannelEncoderInterface
     */
    public function with(ChannelProtocolInterface $protocol);

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
     * @return ChannelProtocolInterface
     */
    public function decode($str);
}
