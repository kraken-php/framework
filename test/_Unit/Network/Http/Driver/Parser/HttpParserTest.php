<?php

namespace Kraken\_Unit\Network\Http\Driver\Parser;

use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Network\Http\Driver\Parser\HttpParser;
use Kraken\Network\Http\Driver\Parser\HttpParserInterface;
use Kraken\Test\TUnit;
use Exception;
use Kraken\Network\Http\HttpRequest;
use Kraken\Network\Http\HttpResponse;
use StdClass;

class HttpParserTest extends TUnit
{
    /**
     * @var HttpParser
     */
    private $parser;

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $parser = $this->createParser();

        $this->assertInstanceOf(HttpParser::class, $parser);
        $this->assertInstanceOf(HttpParserInterface::class, $parser);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $parser = $this->createParser();
        unset($parser);
    }

    /**
     *
     */
    public function testApiParseRequest_ThrowsException_OnInvalidFormat()
    {
        $parser = $this->createParser();

        $this->setExpectedException(InvalidArgumentException::class);
        $parser->parseRequest("GET HTTP/1.1\r\nInvalid Frame");
    }

    /**
     *
     */
    public function testApiParseRequest_CreatesRequestFromString()
    {
        $parser = $this->createParser();

        $method  = 'GET';
        $uri     = '/index.html';
        $headers = [
            'Cache-Control'     => 'no-cache, must-revalidate, max-age=0',
            'Connection'        => 'keep-alive',
            'Content-Encoding'  => 'gzip',
            'Content-Type'      => 'text/html; charset=iso-8859-2',
            'Date'              => 'Tue, 23 Aug 2016 18:27:12 GMT'
        ];
        $body = 'text message';
        $vers = '1.1';

        $req1 = $this->createRequest($method, $uri, $headers, $body, $vers);
        $req2 = $parser->parseRequest($req1->encode());

        $this->assertSame($req1->getHeaders(), $req2->getHeaders());
        $this->assertSame($req1->getMethod(), $req2->getMethod());
        $this->assertSame($req1->getRequestTarget(), $req2->getRequestTarget());
        $this->assertSame($req1->getProtocolVersion(), $req2->getProtocolVersion());
        $this->assertSame($req1->read(), $req2->read());
    }

    /**
     *
     */
    public function testApiParseResponse_ThrowsException_OnInvalidFormat()
    {
        $parser = $this->createParser();

        $this->setExpectedException(InvalidArgumentException::class);
        $parser->parseRequest("HTTP/1.1\r\nInvalid Frame");
    }

    /**
     *
     */
    public function testApiParseResponse_CreatesResponseFromString()
    {
        $parser = $this->createParser();

        $status  = 404;
        $headers = [
            'Cache-Control'     => 'no-cache, must-revalidate, max-age=0',
            'Connection'        => 'keep-alive',
            'Content-Encoding'  => 'gzip',
            'Content-Type'      => 'text/html; charset=iso-8859-2',
            'Date'              => 'Tue, 23 Aug 2016 18:27:12 GMT'
        ];
        $body = 'text message';
        $vers = '1.1';

        $rep1  = $this->createResponse($status, $headers, $body, $vers);
        $rep2 = $parser->parseResponse($rep1->encode());

        $this->assertSame($rep1->getHeaders(), $rep2->getHeaders());
        $this->assertSame($rep1->getProtocolVersion(), $rep2->getProtocolVersion());
        $this->assertSame($rep1->getStatusCode(), $rep2->getStatusCode());
        $this->assertSame($rep1->getReasonPhrase(), $rep2->getReasonPhrase());
        $this->assertSame($rep1->read(), $rep2->read());
    }

    /**
     * @param string $method
     * @param string $uri
     * @param string[] $headers
     * @param string $body
     * @param string $version
     * @return HttpRequest
     */
    public function createRequest($method, $uri, $headers = [], $body = null, $version = '1.1')
    {
        return new HttpRequest($method, $uri, $headers, $body, $version);
    }

    /**
     * @param int $status
     * @param string[] $headers
     * @param string $body
     * @param string $version
     * @return HttpResponse
     */
    public function createResponse($status = 200, $headers = [], $body = null, $version = '1.1')
    {
        return new HttpResponse($status, $headers, $body, $version);
    }

    /**
     * @return HttpParser
     */
    public function createParser()
    {
        $this->parser = new HttpParser();

        return $this->parser;
    }
}
