<?php

namespace Kraken\Util\Parser\Json;

use Kraken\Util\Parser\ParserInterface;

class JsonParser implements ParserInterface
{
    /**
     * @var int
     */
    const DECODE_OBJECT = 1;

    /**
     * @var int
     */
    const DECODE_ARRAY  = 2;

    /**
     * @var int
     */
    protected $flags;

    /**
     * @param int $flags
     */
    public function __construct($flags = self::DECODE_ARRAY)
    {
        $this->flags = $flags;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->flags);
    }

    /**
     * Encodes object as string
     *
     * @param mixed $mixed
     * @return string
     */
    public function encode($mixed)
    {
        return json_encode($mixed);
    }

    /**
     * Decodes object from string
     *
     * @param string $str
     * @return mixed
     */
    public function decode($str)
    {
        if (($this->flags & self::DECODE_ARRAY) === self::DECODE_ARRAY)
        {
            return json_decode($str, true);
        }
        else if (($this->flags & self::DECODE_OBJECT) === self::DECODE_OBJECT)
        {
            return json_decode($str);
        }

        return null;
    }
}
