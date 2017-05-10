<?php
/**
 * Created by PhpStorm.
 * User: tian
 * Date: 2017/5/10
 * Time: 14:01
 */

namespace Kraken\Redis\Protocol\Model;

use Kraken\Redis\Protocol\Data\Serializer\SerializerInterface;

abstract class SimpleModel implements ModelInterface
{
    private $value;

    /**
     * create bulk reply (string reply)
     *
     * @param $data
     */
    public function __construct($value)
    {
        if ($value !== null) {
            $this->value = $value;
        }
    }

    public function raw()
    {
        return $this->value;
    }
}