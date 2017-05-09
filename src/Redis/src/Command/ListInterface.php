<?php

namespace Kraken\Redis\Command;

interface ListCommandInterface
{
    public function lIndex($key,$index);
    public function lInsert($key,$action,$pivot,$value);
    public function lLen($key);
    public function lPop($key);
    public function lPush(array $kvMap);
    public function lPushX($key,$value);
    public function lRange($key,$start,$stop);
    public function lRem($key,$count,$value);
    public function lSet($key,$index,$value);
    public function lTrim($key,$start,$stop);
    public function mGet($key,...$values);
    public function mSet(array $kvMap);
    public function monitor();
    public function move($key,$db);
    public function mSetNx($kvMap);
    public function rPop($key);
    public function rPopLPush($src,$dst);
    public function rPush($key,...$values);
    public function rPushX($key,$value);
}