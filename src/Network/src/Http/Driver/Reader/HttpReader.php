<?php

namespace Kraken\Network\Http\Driver\Reader;

use Dazzle\Throwable\Exception\Runtime\ReadException;
use Dazzle\Throwable\Exception\Logic\InvalidFormatException;
use Kraken\Network\Http\Driver\Parser\HttpParser;
use Kraken\Network\Http\Driver\Parser\HttpParserInterface;
use Kraken\Util\Buffer\BufferInterface;
use GuzzleHttp\Psr7;
use Error;
use Exception;

class HttpReader implements HttpReaderInterface
{
    /**
     * @var int
     */
    const DEFAULT_MAX_SIZE = 0x4000; // 16 kB

    /**
     * @var int
     */
    const DEFAULT_START_LINE_LENGTH = 0x400; // 1 kB

    /**
     * @var string
     */
    const HTTP_EOM = "\r\n\r\n";

    /**
     * @var HttpParserInterface
     */
    protected $parser;

    /**
     * @var int
     */
    protected $maxFrameSize;

    /**
     * @param mixed[] $options
     */
    public function __construct($options = [])
    {
        $this->parser = new HttpParser();

        $this->maxFrameSize = isset($options['maxFrameSize'])
            ? (int) $options['maxFrameSize']
            : self::DEFAULT_MAX_SIZE;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->parser);
        unset($this->maxFrameSize);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function readRequest(BufferInterface $buffer, $data)
    {
        $buffer->push($data);

        if (($position = $buffer->search(self::HTTP_EOM)) === false)
        {
            if ($buffer->length() > $this->maxFrameSize)
            {
                throw new ReadException(
                    sprintf('Message start line exceeded maximum size of %d bytes.', $this->maxFrameSize)
                );
            }

            return null;
        }

        try
        {
            return $this->parser->parseRequest($buffer->drain());
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new InvalidFormatException('Could not parse start line.', 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function readResponse(BufferInterface $buffer, $data)
    {
        $buffer->push($data);

        if (($position = $buffer->search(self::HTTP_EOM)) === false)
        {
            if ($buffer->length() > $this->maxFrameSize)
            {
                throw new ReadException(
                    sprintf('Message start line exceeded maximum size of %d bytes.', $this->maxFrameSize)
                );
            }

            return null;
        }

        try
        {
            return $this->parser->parseResponse($buffer->drain());
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new InvalidFormatException('Could not parse start line.');
    }
}
