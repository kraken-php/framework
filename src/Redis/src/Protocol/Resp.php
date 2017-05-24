<?php
namespace Kraken\Redis\Protocol;

use Clue\Redis\Protocol\Model\Request;
use Clue\Redis\Protocol\Parser\RequestParser;
use Clue\Redis\Protocol\Parser\ResponseParser;
use Clue\Redis\Protocol\Serializer\RecursiveSerializer;

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
     * @inheritDoc
     */
    public function commands(Request $request)
    {

        $args = $request->getArgs();
        $command= $request->getCommand();

        return $this->serializer->getRequestMessage($command, $args);
    }
    /**
     * @inheritDoc
     */
    public function replies($data)
    {
        $model = $this->serializer->createReplyModel($data);

        return $model;
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
    /**
     * @param $data
     */
    public function parseResponse($data)
    {
        return $this->responseParser->pushIncoming($data);
    }
    /**
     * @param $data
     * @return string
     */
    public function buildResponse($data)
    {
        return $this->serializer->getReplyMessage($data);
    }

}