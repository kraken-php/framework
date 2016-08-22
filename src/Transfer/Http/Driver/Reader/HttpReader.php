<?php

namespace Kraken\Transfer\Http\Driver\Reader;

use Kraken\Throwable\Exception\Runtime\Io\IoReadException;
use Kraken\Throwable\Exception\Logic\InvalidFormatException;
use Kraken\Transfer\Http\Driver\Parser\HttpParser;
use Kraken\Transfer\Http\Driver\Parser\HttpParserInterface;
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
    protected $maxStartLineLength;

    /**
     * @var int
     */
    protected $maxSize;

    /**
     * @param mixed[] $options
     */
    public function __construct($options = [])
    {
        $this->parser = new HttpParser();

        $this->maxSize = isset($options['max_header_size'])
            ? (int) $options['max_header_size']
            : self::DEFAULT_MAX_SIZE;

        $this->maxStartLineLength = isset($options['max_start_line_length'])
            ? (int) $options['max_start_line_length']
            : self::DEFAULT_START_LINE_LENGTH;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->parser);
        unset($this->maxStartLineLength);
        unset($this->maxSize);
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
            if ($buffer->length() > $this->maxStartLineLength)
            {
                throw new IoReadException(
                    sprintf('Message start line exceeded maximum size of %d bytes.', $this->maxStartLineLength)
                );
            }

            return null;
        }

        try
        {
            $request = $this->parser->parseRequest($buffer->drain());
        }
        catch (Error $ex)
        {
            throw new InvalidFormatException('Could not parse start line.');
        }
        catch (Exception $ex)
        {
            throw new InvalidFormatException('Could not parse start line.');
        }

        return $request;
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
            if ($buffer->length() > $this->maxStartLineLength)
            {
                throw new IoReadException(
                    sprintf('Message start line exceeded maximum size of %d bytes.', $this->maxStartLineLength)
                );
            }

            return null;
        }

        try
        {
            $response = $this->parser->parseResponse($buffer->drain());
        }
        catch (Error $ex)
        {
            throw new InvalidFormatException('Could not parse start line.');
        }
        catch (Exception $ex)
        {
            throw new InvalidFormatException('Could not parse start line.');
        }

        return $response;
    }
}
