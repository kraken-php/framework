<?php

namespace Kraken\Channel\Encoder;

use Kraken\Channel\Protocol\ProtocolInterface;
use Dazzle\Util\Parser\ParserInterface;

class Encoder implements EncoderInterface
{
    /**
     * @var ParserInterface
     */
    protected $parser;

    /**
     * @var ProtocolInterface
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
    public function with(ProtocolInterface $protocol)
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
