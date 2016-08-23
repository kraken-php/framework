<?php

namespace Kraken\Transfer\Http\Driver;

use Kraken\Transfer\Http\Driver\Reader\HttpReader;
use Kraken\Transfer\Http\Driver\Reader\HttpReaderInterface;
use Kraken\Util\Buffer\BufferInterface;

class HttpDriver implements HttpDriverInterface
{
    /**
     * @var mixed[]
     */
    protected $options;

    /**
     * @var HttpReaderInterface
     */
    protected $reader;

    /**
     * @param mixed[] $options
     */
    public function __construct($options = [])
    {
        $this->options = $options;
        $this->reader = new HttpReader($this->options);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->options);
        unset($this->reader);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function readRequest(BufferInterface $buffer, $message)
    {
        return $this->reader->readRequest($buffer, $message);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function readResponse(BufferInterface $buffer, $message)
    {
        return $this->reader->readResponse($buffer, $message);
    }
}
