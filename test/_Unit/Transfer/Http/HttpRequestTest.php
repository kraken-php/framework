<?php

namespace Kraken\_Unit\Transfer\Http;

use Kraken\Transfer\Http\HttpRequest;
use Kraken\Transfer\Http\HttpRequestInterface;
use Kraken\Transfer\TransferMessageInterface;
use Kraken\Test\TUnit;

class HttpRequestTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $req = $this->createRequest('method', 'uri');

        $this->assertInstanceOf(HttpRequest::class, $req);
        $this->assertInstanceOf(HttpRequestInterface::class, $req);
        $this->assertInstanceOf(TransferMessageInterface::class, $req);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $req = $this->createRequest('method', 'uri');
        unset($req);
    }

    /**
     *
     */
    public function testApiToString_EncodesProtocol()
    {
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
        $req  = $this->createRequest($method, $uri, $headers, $body, $vers);

        $expected = sprintf(
            "%s %s HTTP/%s\r\n%s\r\n%s",
            $method,
            $uri,
            $vers,
            $this->callProtectedMethod($req, 'encodeHeaders', [ $headers ]),
            $body
        );

        $this->assertSame($expected, (string) $req);
    }

    /**
     *
     */
    public function testApiEncode_EncodesProtocol()
    {
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
        $req  = $this->createRequest($method, $uri, $headers, $body, $vers);

        $expected = sprintf(
            "%s %s HTTP/%s\r\n%s\r\n%s",
            $method,
            $uri,
            $vers,
            $this->callProtectedMethod($req, 'encodeHeaders', [ $headers ]),
            $body
        );

        $this->assertSame($expected, $req->encode());
    }

    /**
     *
     */
    public function testApiRead_ReturnsBody()
    {
        $body = 'text message';
        $req  = $this->createRequest('method', 'uri', [], $body);

        $this->assertSame($body, $req->read());
    }

    /**
     *
     */
    public function testProtectedApiEncodeHeaders_EncodesMultipleHeaders()
    {
        $req = $this->createRequest('method', 'uri');

        $headers = [
            'Cache-Control'     => [ 'no-cache', 'must-revalidate', 'max-age=0' ],
            'Connection'        => [ 'keep-alive' ],
            'Content-Encoding'  => [ 'gzip' ],
            'Content-Type'      => [ 'text/html; charset=iso-8859-2' ],
            'Date'              => [ 'Tue, 23 Aug 2016 18:27:12 GMT' ]
        ];
        $expected = '';
        $expected .= "Cache-Control: no-cache, must-revalidate, max-age=0\r\n";
        $expected .= "Connection: keep-alive\r\n";
        $expected .= "Content-Encoding: gzip\r\n";
        $expected .= "Content-Type: text/html; charset=iso-8859-2\r\n";
        $expected .= "Date: Tue, 23 Aug 2016 18:27:12 GMT\r\n";

        $result = $this->callProtectedMethod($req, 'encodeHeaders', [ $headers ]);

        $this->assertSame($expected, $result);
    }

    /**
     *
     */
    public function testProtectedApiEncodeHeader_EncodesSingleHeader()
    {
        $req = $this->createRequest('method', 'uri');

        $header = 'text/html; charset=iso-8859-2';
        $result = $this->callProtectedMethod($req, 'encodeHeader', [ $header ]);

        $this->assertSame($header, $result);
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
}
