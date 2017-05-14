<?php

namespace Kraken\Redis;

use Kraken\Event\EventEmitterInterface;
use Kraken\Loop\Loop;
use Kraken\Promise\Promise;
use Kraken\Promise\Deferred;
use Kraken\Ipc\Socket\Socket;
use Kraken\Event\EventEmitter;
use Kraken\Loop\LoopInterface;
use Kraken\Loop\Model\SelectLoop;
use Kraken\Promise\PromiseInterface;
use Kraken\Redis\Protocol\Data\Arrays;
use Kraken\Redis\Protocol\Data\Errors;
use Kraken\Redis\Protocol\Model\ModelInterface;
use Kraken\Redis\Protocol\Data\SimpleStrings;
use UnderflowException;
use RuntimeException;

class Client extends ClientStub implements EventEmitterInterface,ClientStubInterface,ClientInterface
{
    private $ending = false;
    private $closed = false;
    private $subscribed = 0;
    private $psubscribed = 0;
    private $monitoring = false;

    /**
     * @overwrite
     * @param string $uri
     * @param LoopInterface $loop
     */
    public function __construct($uri, LoopInterface $loop)
    {
        $this->uri = $uri;
        parent::__construct($loop);
    }

    public function connect()
    {
        return parent::create($this->uri);
    }

    public function handleMessage(ModelInterface $message)
    {
        $this->on('close',[$this,'end']);

        $this->emit('data', array($message));

        if ($this->monitoring && $this->isMonitorMessage($message)) {
            $this->emit('monitor', array($message));
            return;
        }

        if (($this->subscribed !== 0 || $this->psubscribed !== 0) && $message instanceof Arrays) {
            $array = $message->raw();
            $first = array_shift($array);

            // pub/sub messages are to be forwarded and should not be processed as request responses
            if (in_array($first, array('message', 'pmessage'))) {
                $this->emit($first, $array);
                return;
            }
        }

        if (!$this->requests) {
            throw new UnderflowException('Unexpected reply received, no matching request found');
        }

        $request = array_shift($this->requests);

        /* @var Deferred $request */

        if ($message instanceof Errors) {
            $request->reject($message);
        } else {
            $request->resolve($message->raw());
        }

        if ($this->ending && !$this->isBusy()) {
            $this->emit('close');
        }
    }

    private function isMonitorMessage(ModelInterface $message)
    {
        // Check status '1409172115.207170 [0 127.0.0.1:58567] "ping"' contains otherwise uncommon '] "'
        return ($message instanceof SimpleStrings && strpos($message->raw(), '] "') !== false);
    }

    public function isBusy()
    {
        return empty($this->requests) ? false : true;
    }

    public function end()
    {
        $this->ending = true;
        if (!$this->isBusy()) {
            $this->close();
        }
    }

    public function close()
    {
        if ($this->closed) {
            return;
        }
        $this->ending = true;
        $this->closed = true;
        $this->getStream()->close();

        $this->emit('close');

        // reject all remaining requests in the queue
        while($this->requests) {
            $request = array_shift($this->requests);
            /* @var $request Deferred */
            $request->reject(new RuntimeException('Connection closing'));
        }
        /**
         * @var Loop $loop
         */
        $loop  = $this->getLoop();
        $loop->stop();
    }

    public function reply()
    {
        /**
         * @var Loop $loop
         */
        $loop  = $this->getLoop();
        $loop->start();
    }

    /**
     * @overwrite
     * @param $password
     */
    public function auth($password)
    {
        $request = parent::auth($password);

        return $this->dispatch($request);
    }

    public function append($key, $value)
    {
        $request = parent::append($key, $value);

        return $this->dispatch($request);
    }

    public function bgRewriteAoF()
    {
    }

    public function bgSave()
    {
    }

    public function bitCount($key, $start = 0, $end = 0)
    {
    }

    public function bitField($command, ...$param)
    {
    }

    public function bitOp($operation, $dstKey, ...$keys)
    {
    }

    public function bitPos($key, $bit, $start = 0, $end = 0)
    {
    }

    public function blPop(array $keys, $timeout)
    {
    }

    public function brPop(array $keys, $timeout)
    {
    }

    public function brPopLPush($src, $dst, $timeout)
    {
    }

    public function decr($key)
    {
    }

    public function decrBy($key, $decrement)
    {
    }

    public function discard()
    {
    }

    public function dump($key)
    {
    }

    public function exists(...$keys)
    {
    }

    public function expire($key, $seconds)
    {
    }

    public function expireAt($key, $timestamp)
    {
    }

    public function get($key)
    {
    }

    public function getBit($key, $offset)
    {
    }

    public function getRange($key, $start, $end)
    {
    }

    public function getSet($key, $value)
    {
    }

    public function incr($key)
    {
    }

    public function incrBy($key, $increment)
    {
    }

    public function incrByFloat($key, $increment)
    {
    }

    public function multi()
    {
    }

    public function persist($key)
    {
    }

    public function pExpire($key, $milliseconds)
    {
    }

    public function pExpireAt($key, $milliseconds)
    {
    }

    public function sync()
    {
    }

    public function time()
    {
    }

    public function touch(...$keys)
    {
    }

    public function ttl($key)
    {
    }

    public function type($key)
    {
    }

    public function unLink(...$keys)
    {
    }

    public function unWatch()
    {
    }

    public function wait($numSlaves, $timeout)
    {
    }

    public function watch(...$keys)
    {
    }

    public function select($index)
    {
    }

    public function set($key, $value, array $options)
    {
    }

    public function setBit($key, $offset, $value)
    {
    }

    public function setEx($key, $seconds, $value)
    {
    }

    public function setNx($key, $value)
    {
    }

    public function randomKey()
    {
    }

    public function readOnly()
    {
    }

    public function readWrtie()
    {
    }

    public function rename($key, $newKey)
    {
    }

    public function renameNx($key, $newKey)
    {
    }

    public function restore($key, $ttl, $value)
    {
    }

    public function ping($message = 'pong')
    {
    }

    public function quit()
    {
    }

    public function setRange($key, $offset, $value)
    {
    }

    public function pTtl($key)
    {
    }

    public function pSetEx($key, $milliseconds, $value)
    {
    }
};