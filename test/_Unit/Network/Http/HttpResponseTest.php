<?php

namespace Kraken\_Unit\Network\Http;

use Kraken\Network\Http\HttpResponse;
use Kraken\Network\Http\HttpResponseInterface;
use Kraken\Network\NetworkMessageInterface;
use Kraken\Test\TUnit;

class HttpResponseTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $req = $this->createResponse();

        $this->assertInstanceOf(HttpResponse::class, $req);
        $this->assertInstanceOf(HttpResponseInterface::class, $req);
        $this->assertInstanceOf(NetworkMessageInterface::class, $req);
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $req = $this->createResponse();
        unset($req);
    }

    /**
     *
     */
    public function testApiToString_EncodesProtocol()
    {
        $status = 404;
        $headers = [
            'Cache-Control'     => 'no-cache, must-revalidate, max-age=0',
            'Connection'        => 'keep-alive',
            'Content-Encoding'  => 'gzip',
            'Content-Type'      => 'text/html; charset=iso-8859-2',
            'Date'              => 'Tue, 23 Aug 2016 18:27:12 GMT'
        ];
        $body = 'text message';
        $vers = '1.1';
        $req  = $this->createResponse($status, $headers, $body, $vers);

        $expected = sprintf(
            "HTTP/%s %d %s\r\n%s\r\n%s",
            $vers,
            (string) $status,
            'Not Found',
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
        $req  = $this->createResponse($status, $headers, $body, $vers);

        $expected = sprintf(
            "HTTP/%s %d %s\r\n%s\r\n%s",
            $vers,
            (string) $status,
            'Not Found',
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
        $req  = $this->createResponse(200, [], $body);

        $this->assertSame($body, $req->read());
    }

    /**
     *
     */
    public function testProtectedApiEncodeHeaders_EncodesMultipleHeaders()
    {
        $req = $this->createResponse();

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
        $req = $this->createResponse();

        $header = 'text/html; charset=iso-8859-2';
        $result = $this->callProtectedMethod($req, 'encodeHeader', [ $header ]);

        $this->assertSame($header, $result);
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
}
