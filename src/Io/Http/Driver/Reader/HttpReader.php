<?php

namespace Kraken\Io\Http\Driver\Reader;

use Error;
use Kraken\Throwable\Exception\Runtime\Io\IoReadException;
use Kraken\Throwable\Exception\Logic\InvalidFormatException;
use Kraken\Io\Http\Driver\Parser\HttpParser;
use Kraken\Io\Http\Driver\Parser\HttpParserInterface;
use Kraken\Util\Buffer\BufferInterface;
use GuzzleHttp\Psr7;
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

//    /**
//     * {@inheritdoc}
//     */
//    public function readResponse(Socket $socket, $timeout = 0)
//    {
//        $buffer = new Buffer();
//
//        do {
//            $buffer->push(yield $socket->read(0, null, $timeout));
//        } while (false === ($position = $buffer->search("\r\n")) && $buffer->getLength() < $this->maxStartLineLength);
//
//        if (false === $position) {
//            throw new MessageException(
//                Response::REQUEST_HEADER_TOO_LARGE,
//                sprintf('Message start line exceeded maximum size of %d bytes.', $this->maxStartLineLength)
//            );
//        }
//
//        $line = $buffer->shift($position + 2);
//
//        if (!preg_match("/^HTTP\/(\d+(?:\.\d+)?) (\d{3})(?: (.+))?\r\n$/i", $line, $matches)) {
//            throw new ParseException('Could not parse start line.');
//        }
//
//        $protocol = $matches[1];
//        $code = (int) $matches[2];
//        $reason = isset($matches[3]) ? $matches[3] : '';
//
//        $headers = (yield $this->readHeaders($buffer, $socket, $timeout));
//
//        if ($buffer->getLength()) {
//            $socket->unshift((string) $buffer);
//        }
//
//        yield new BasicResponse($code, $headers, $socket, $reason, $protocol);
//    }
//
//    /**
//     * @override
//     */
//    public function sreadRequest(SocketInterface $socket)
//    {
//        $buffer = new BufferMemorySoft();
//
//        do
//        {
//            $buffer->push($socket->read());
//        }
//        while (false === ($position = $buffer->search("\r\n")) && $buffer->getLength() < $this->maxStartLineLength);
//
//        if (false === $position)
//        {
//            throw new IoReadException(
//                sprintf('Message start line exceeded maximum size of %d bytes.', $this->maxStartLineLength)
//            );
//        }
//
//        $line = $buffer->shift($position + 2);
//
//        if (!preg_match("/^([A-Z]+) (\S+) HTTP\/(\d+(?:\.\d+)?)\r\n$/i", $line, $matches))
//        {
//            throw new InvalidFormatException('Could not parse start line.');
//        }
//
//        $method = $matches[1];
//        $target = $matches[2];
//        $protocol = $matches[3];
//
//        $headers = $this->readHeaders($buffer, $socket);
//
//        if ($buffer->getLength())
//        {
//            $socket->unshift((string) $buffer);
//        }
//
//        if ('/' === $target[0])
//        {
//            $uri = new BasicUri($this->filterHost($this->findHost($headers)) . $target);
//            $target = null; // Empty request target since it was a path.
//        }
//        else if ('*' === $target)
//        {
//            $uri = new BasicUri($this->filterHost($this->findHost($headers)));
//        }
//        else if (preg_match('/^https?:\/\//i', $target))
//        {
//            $uri = new BasicUri($target);
//        }
//        else
//        {
//            $uri = new BasicUri($this->filterHost($target));
//        }
//
//        new BasicRequest($method, $uri, $headers, $socket, $target, $protocol);
//    }
//
//    /**
//     * @param \Icicle\Stream\Structures\Buffer $buffer
//     * @param \Icicle\Socket\Socket $socket
//     * @param float|int $timeout
//     *
//     * @return \Generator
//     *
//     * @throws \Icicle\Http\Exception\MessageException
//     * @throws \Icicle\Http\Exception\ParseException
//     */
//    protected function readHeaders(BufferInterface $buffer, SocketInterface $socket)
//    {
//        $size = 0;
//        $headers = [];
//
//        do
//        {
//            while (false === ($position = $buffer->search("\r\n")))
//            {
//                if ($buffer->getLength() >= $this->maxSize)
//                {
//                    throw new IoReadException(
//                        sprintf('Message header exceeded maximum size of %d bytes.', $this->maxSize)
//                    );
//                }
//
//                $buffer->push($socket->read());
//            }
//
//            $length = $position + 2;
//            $line = $buffer->shift($length);
//
//            if (2 === $length)
//            {
//                return $headers;
//            }
//
//            $size += $length;
//
//            $parts = explode(':', $line, 2);
//
//            if (2 !== count($parts))
//            {
//                throw new InvalidFormatException('Found header without colon.');
//            }
//
//            list($name, $value) = $parts;
//            $value = trim($value);
//
//            // No check for case as Message class will automatically combine similarly named headers.
//            if (!isset($headers[$name]))
//            {
//                $headers[$name] = [ $value ];
//            }
//            else
//            {
//                $headers[$name][] = $value;
//            }
//        }
//        while ($size < $this->maxSize);
//
//        throw new IoReadException(
//            sprintf('Message header exceeded maximum size of %d bytes.', $this->maxSize)
//        );
//    }
//
//    /**
//     * @param string $host
//     * @return string
//     */
//    protected function filterHost($host)
//    {
//        if (strrpos($host, ':', -1))
//        {
//            return $host;
//        }
//
//        return '//' . $host;
//    }
//
//    /**
//     * @param string[][] $headers
//     * @return string
//     * @throws IoReadException
//     */
//    protected function findHost($headers)
//    {
//        foreach ($headers as $name=>$values)
//        {
//            if (strtolower($name) === 'host')
//            {
//                return $values[0];
//            }
//        }
//
//        throw new IoReadException('No host header in message.');
//    }
}
