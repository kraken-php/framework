<?php

namespace Kraken\Pattern\Collection;

use SplFixedArray;

class FixedArray extends SplFixedArray
{
    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $step;

    /**
     * @param int $size
     * @param int $step
     */
    public function __construct($size = 0, $step = 1000)
    {
        parent::__construct($size);

        $this->size = $size;
        $this->step = $step;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->size);
        unset($this->step);
    }

    /**
     * @param int $index
     * @param mixed $newval
     */
    public function offsetSet($index, $newval)
    {
        if ($index === null)
        {
            $index = $this->getSize();
        }
        if ($index >= $this->size)
        {
            $this->size = ((int)($index / $this->step) + 1) * $this->step;
            $this->setSize($this->size);
        }
        parent::offsetSet($index, $newval);
    }
}