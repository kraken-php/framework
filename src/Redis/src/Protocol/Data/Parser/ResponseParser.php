<?php

namespace Kraken\Redis\Protocol\Data\Parser;

use Kraken\Redis\Protocol\Data\Arrays;
use Kraken\Redis\Protocol\Data\Integers;
use Kraken\Redis\Protocol\Data\Parser\ParserInterface;
use Kraken\Redis\Protocol\Model\ModelInterface;
use Kraken\Redis\Protocol\Data\BulkStrings;
use Kraken\Redis\Protocol\Data\Errors;
use Kraken\Redis\Protocol\Data\SimpleStrings;
use Clue\Redis\Protocol\Parser\ParserException;

/**
 * Simple recursive redis wire protocol parser
 *
 * Heavily influenced by blocking parser implementation from jpd/redisent.
 *
 * @link https://github.com/jdp/redisent
 * @link http://redis.io/topics/protocol
 */
class ResponseParser implements ParserInterface
{
    private $incomingBuffer = '';
    private $incomingOffset = 0;

    public function pushIncoming($dataChunk)
    {
        $this->incomingBuffer .= $dataChunk;

        return $this->tryParsingIncomingMessages();
    }

    private function tryParsingIncomingMessages()
    {
        $messages = array();

        do {
            $message = $this->readResponse();
            if ($message === null) {
                // restore previous position for next parsing attempt
                $this->incomingOffset = 0;
                break;
            }

            $messages []= $message;

            $this->incomingBuffer = (string)substr($this->incomingBuffer, $this->incomingOffset);
            $this->incomingOffset = 0;
        } while($this->incomingBuffer !== '');

        return $messages;
    }

    private function readLine()
    {
        $pos = strpos($this->incomingBuffer, static::CRLF, $this->incomingOffset);

        if ($pos === false) {
            return null;
        }

        $ret = (string)substr($this->incomingBuffer, $this->incomingOffset, $pos - $this->incomingOffset);
        $this->incomingOffset = $pos + 2;

        return $ret;
    }

    private function readLength($len)
    {
        $ret = substr($this->incomingBuffer, $this->incomingOffset, $len);
        if (strlen($ret) !== $len) {
            return null;
        }

        $this->incomingOffset += $len;

        return $ret;
    }

    /**
     * try to parse response from incoming buffer
     *
     * ripped from jdp/redisent, with some minor modifications to read from
     * the incoming buffer instead of issuing a blocking fread on a stream
     *
     * @throws ParserException if the incoming buffer is invalid
     * @return ModelInterface|null
     * @link https://github.com/jdp/redisent
     */
    private function readResponse() {
        /* Parse the response based on the reply identifier */
        $reply = $this->readLine();
        if ($reply === null) {
            return null;
        }
        switch (substr($reply, 0, 1)) {
            /* Error reply */
            case '-':
                $response = new Errors(substr($reply, 1));
                break;
                /* Inline reply */
            case '+':
                $response = new SimpleStrings(substr($reply, 1));
                break;
                /* Bulk reply */
            case '$':
                $size = (int)substr($reply, 1);
                if ($size === -1) {
                    return new BulkStrings(null);
                }
                $data = $this->readLength($size);
                if ($data === null) {
                    return null;
                }
                if ($this->readLength(2) === null) { /* discard crlf */
                    return null;
                }
                $response = new BulkStrings($data);
                break;
                /* Multi-bulk reply */
            case '*':
                $count = (int)substr($reply, 1);
                if ($count === -1) {
                    return new MultiBulkReply(null);
                }
                $response = array();
                for ($i = 0; $i < $count; $i++) {
                    $sub = $this->readResponse();
                    if ($sub === null) {
                        return null;
                    }
                    $response []= $sub;
                }
                $response = new Arrays($response);
                break;
                /* Integer reply */
            case ':':
                $response = new Integers(substr($reply, 1));
                break;
            default:
                throw new ParserException('Invalid message can not be parsed: "' . $reply . '"');
                break;
        }
        /* Party on */
        return $response;
    }
}
