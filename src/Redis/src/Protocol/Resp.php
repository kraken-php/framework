<?php
namespace Kraken\Redis\Protocol;

use Kraken\Redis\Protocol\Data\Parser\RequestParser;
use Kraken\Redis\Protocol\Data\Parser\ResponseParser;
use Kraken\Redis\Protocol\Data\Serializer\RecursiveSerializer;

class Resp implements RespProtocol
{
    /**
     * @var RequestParser
     */
    private $requestParser;
    /**
     * @var ResponseParser
     */
    private $responseParser;
    /**
     * @var RecursiveSerializer
     */
    private $serializer;

    public function __construct()
    {
        $this->requestParser = new RequestParser();
        $this->responseParser = new ResponseParser();
        $this->serializer = new RecursiveSerializer();
    }
    /**
     * make request object
     * @inheritDoc
     */
    public function commands($data)
    {
        return $this->requestParser->pushIncoming($data);
    }
    /**
     * make response object
     * @inheritDoc
     */
    public function replies($data)
    {
        return $this->responseParser->pushIncoming($data);
    }
    /**
     * @return RecursiveSerializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }
    /**
     * @return RequestParser
     */
    public function getRequestParser()
    {
        return $this->requestParser;
    }
    /**
     * @return ResponseParser
     */
    public function getResponseParser()
    {
        return $this->responseParser;
    }

}