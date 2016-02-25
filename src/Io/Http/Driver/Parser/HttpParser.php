<?php

namespace Kraken\Io\Http\Driver\Parser;

use Kraken\Io\Http\HttpRequest;
use Kraken\Io\Http\HttpResponse;
use GuzzleHttp\Psr7;

class HttpParser implements HttpParserInterface
{
    /**
     * @override
     */
    public function parseRequest($message)
    {
        $data = Psr7\_parse_message($message);
        $matches = [];
        if (!preg_match('/^[a-zA-Z]+\s+([a-zA-Z]+:\/\/|\/).*/', $data['start-line'], $matches)) {
            throw new \InvalidArgumentException('Invalid request string');
        }
        $parts = explode(' ', $data['start-line'], 3);
        $version = isset($parts[2]) ? explode('/', $parts[2])[1] : '1.1';

        $request = new HttpRequest(
            $parts[0],
            $matches[1] === '/' ? Psr7\_parse_request_uri($parts[1], $data['headers']) : $parts[1],
            $data['headers'],
            $data['body'],
            $version
        );

        return $matches[1] === '/' ? $request : $request->withRequestTarget($parts[1]);
    }

    /**
     * @override
     */
    public function parseResponse($message)
    {
        $data = Psr7\_parse_message($message);
        if (!preg_match('/^HTTP\/.* [0-9]{3} .*/', $data['start-line'])) {
            throw new \InvalidArgumentException('Invalid response string');
        }
        $parts = explode(' ', $data['start-line'], 3);

        return new HttpResponse(
            $parts[1],
            $data['headers'],
            $data['body'],
            explode('/', $parts[0])[1],
            isset($parts[2]) ? $parts[2] : null
        );
    }
}
