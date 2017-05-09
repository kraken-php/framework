<?php

namespace Kraken\Redis\Command;

interface ChannelInterface
{
    public function pSubscribe(...$patterns);
    public function pubSub($command,array $args = []);
    public function publish($channel,$message);
    public function pUnsubscribe(...$patterns);
    public function unSubscribe(...$channels);
}