<?php

namespace Kraken\Channel;

use Kraken\Parser\ParserInterface;

class ChannelEncoder implements ChannelEncoderInterface
{
    /**
     * @var ParserInterface
     */
    protected $parser;

    /**
     * @var ChannelProtocolInterface
     */
    protected $protocol;

    /**
     * @param ParserInterface $parser
     */
    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
        $this->protocol = null;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->parser);
        unset($this->protocol);
    }

    /**
     * @param ChannelProtocolInterface $protocol
     * @return ChannelEncoderInterface
     */
    public function with(ChannelProtocolInterface $protocol)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * @return string
     */
    public function encode()
    {
        return $this->parser->encode($this->protocol->getAll());
    }

    /**
     * @param $str
     * @return ChannelProtocolInterface
     */
    public function decode($str)
    {
        return $this->protocol->setAll($this->parser->decode($str));
    }
}
