<?php

namespace Kraken\Transfer\Http;

use Kraken\Transfer\IoMessageInterface;
use GuzzleHttp\Psr7\Response;

class HttpResponse extends Response implements HttpResponseInterface, IoMessageInterface
{
    /**
     * @override
     */
    public function __toString()
    {
        return $this->encode();
    }

    /**
     * @override
     */
    public function encode()
    {
        return sprintf(
            "HTTP/%s %d %s\r\n%s\r\n%s",
            $this->getProtocolVersion(),
            $this->getStatusCode(),
            $this->getReasonPhrase(),
            $this->encodeHeaders($this->getHeaders()),
            (string) $this->getBody()
        );
    }

    /**
     * @override
     */
    public function read()
    {
        return (string) $this->getBody();
    }

    /**
     * @param string[][] $headers
     * @return string
     */
    protected function encodeHeaders($headers = [])
    {
        $data = '';

        foreach ($headers as $name=>$values)
        {
            foreach ($values as $value)
            {
                $data .= sprintf("%s: %s\r\n", $name, $this->encodeHeader($value));
            }
        }

        return $data;
    }

    /**
     * @param string $header
     * @return string
     */
    protected function encodeHeader($header)
    {
        return preg_replace_callback(
            '/(?:[^A-Za-z0-9_\-\.~!\$&\'\(\)\[\]\*\+,:;=\/% ]+|%(?![A-Fa-f0-9]{2}))/',
            function (array $matches) {
                return rawurlencode($matches[0]);
            },
            $header
        );
    }
}
