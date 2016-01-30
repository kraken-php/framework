<?php

namespace Kraken\Channel;

interface ChannelEncoderInterface
{
    /**
     * @param ChannelProtocolInterface $protocol
     * @return ChannelEncoderInterface
     */
    public function with(ChannelProtocolInterface $protocol);

    /**
     * @return string
     */
    public function encode();

    /**
     * @param $str
     * @return ChannelProtocolInterface
     */
    public function decode($str);
}
