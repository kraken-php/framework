<?php

namespace Kraken\Redis\Command;

interface HashCommandInterface
{
    public function hDel($key,...$fields);
    public function hExsits($key,$field);
    public function hGet($key,$field);
    public function hGetAll($key);
    public function hIncrBy($key,$field,$incrment);
    public function hIncrByFloat($key,$field,$increment);
    public function hKeys($key);
    public function hLen($key);
    public function hMGet($key,...$fields);
    public function hMSet($key,array $fvMap);
    public function hSet($key,$field,$value);
    public function hSetNx($key,$filed,$value);
    public function hStrLen($key,$field);
    public function hVals($key);
}