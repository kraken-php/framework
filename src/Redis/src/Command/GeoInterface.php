<?php

namespace Kraken\Redis\Command;

interface GeoInterface
{
    public function geoAdd($key,array $coordinates);
    public function geoHash($key,...$members);
    public function geoPos($key,...$members);
    public function geoDist($key,$memberA,$memberB,$unit);
    public function geoRadius($key,$longitude,$latitude,$unit,$command,$count,$sort);
    public function geoRadiusByMember($key,$member,$unit,$command,$count,$sort,$store,$storeDist);
}