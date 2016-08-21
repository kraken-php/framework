<?php

namespace Kraken\Channel;

use Kraken\Util\Parser\ParserInterface;

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
     * @override
     * @inheritDoc
     */
    public function with(ChannelProtocolInterface $protocol)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function encode()
    {
        return $this->parser->encode($this->protocol->getAll());
    }

    /**
     * @override
     * @inheritDoc
     */
    public function decode($str)
    {
        return $this->protocol->setAll($this->parser->decode($str));
    }
}
