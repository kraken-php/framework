<?php

namespace Kraken\Redis\Command;

interface SetInterface
{
    public function zAdd($key,array $options = []);
    public function zCard($key);
    public function zCount($key,$min,$max);
    public function zIncrBy($key,$increment,$member);
    public function zInterStore($dst,$numKeys);
    public function zLexCount($key,$min,$max);
    public function zRange($key,$star,$stop,array $options = []);
    public function zRangeByLex($key,$min,$max,array $options = []);
    public function zRevRangeByLex($key,$max,$min,array $options = []);
    public function zRangeByScore($key,$min,$max,array $options = []);
    public function zRank($key,$member);
    public function zRem($key,...$members);
    public function zRemRangeByLex($key,$min,$max);
    public function zRemRangeByRank($key,$start,$stop);
    public function zRemRangeByScore($key,$min,$max);
    public function zRevRange($key,$start,$stop,array $options = []);
    public function zRevRangeByScore($key,$max,$min,array $options = []);
    public function zRevRank($key,$member);
    public function zScore($key,$member);
    public function zUniionScore($dst,$numKeys);
    public function scan($cursor,array $options = []);
    public function sScan($key,$cursor,array $options = []);
    public function hScan($key,$cursor,array $options = []);
    public function zScan($key,$cursor,array $options = []);
    public function sInter(...$keys);
    public function sInterStore($dst,...$keys);
    public function sIsMember($key,$member);
    public function slaveOf($host,$port);
    public function sLowLog($command,array $args=[]);
    public function sMembers($key);
    public function sMove($src,$dst,$members);
    public function sort($key,array $options = []);
    public function sPop($key,$count);
    public function sRandMember($key,$count);
    public function sRem($key,...$members);
    public function strLen($key);
    public function subscribe(...$channels);
    public function sUnion(...$keys);
    public function sUnionStore($dst,...$keys);
    public function sWapBb($opt,$dst,...$keys);
    public function sAdd($key,...$members);
    public function save();
    public function sCard($key);
    public function sDiff(...$keys);
    public function sDiffStore($dst,...$keys);
}