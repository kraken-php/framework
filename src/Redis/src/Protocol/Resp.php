<?php
namespace Kraken\Redis\Protocol;

use Exception;
use InvalidArgumentException;
use Kraken\Redis\Protocol\Data\Errors;
use Kraken\Redis\Protocol\Data\Arrays;
use Kraken\Redis\Protocol\Data\Integers;
use Kraken\Redis\Protocol\Data\BulkStrings;
use Kraken\Redis\Protocol\Data\Request;
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
     * @inheritDoc
     */
    public function commands(Request $request)
    {
        $args = $request->getArgs();
        $command= $request->getCommand();
        $data = '*' . (count($args) + 1) . RecursiveSerializer::CRLF .
            "$" . strlen($command) . RecursiveSerializer::CRLF . $command . RecursiveSerializer::CRLF;
        foreach ($args as $arg) {
            $data .= '$' . strlen($arg) . RecursiveSerializer::CRLF . $arg . RecursiveSerializer::CRLF;
        }

        return $data;
    }
    /**
     * @inheritDoc
     */
    public function replies($data)
    {
        if (is_string($data) || $data === null) {
            return new BulkStrings($data);
        } else if (is_int($data) || is_float($data) || is_bool($data)) {
            return new Integers($data);
        } else if ($data instanceof Exception) {
            return new Errors($data->getMessage());
        } else if (is_array($data)) {
            $models = array();
            foreach ($data as $one) {
                $models []= $this->buildResponse($one);
            }
            return new Arrays($models);
        } else {
            throw new InvalidArgumentException('Invalid data type passed for serialization');
        }
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