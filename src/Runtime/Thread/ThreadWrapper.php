<?php

namespace Kraken\Runtime\Thread;

use Thread;

class ThreadWrapper extends Thread
{
    /**
     * @var string
     */
    public $datapath;

    /**
     * @var string
     */
    public $parent;

    /**
     * @var string
     */
    public $alias;

    /**
     * @var string
     */
    public $name;

    /**
     * @param string $datapath
     * @param string $parent
     * @param string $alias
     * @param string $name
     */
    public function __construct($datapath, $parent, $alias, $name)
    {
        $this->datapath = $datapath;
        $this->parent = $parent;
        $this->alias = $alias;
        $this->name = $name;
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->datapath);
        unset($this->parent);
        unset($this->alias);
        unset($this->name);
    }

    /**
     *
     */
    public function run()
    {
        $parent = $this->parent;
        $alias  = $this->alias;
        $name   = $this->name;

        require(
            $this->datapath . '/autorun/kraken.thread'
        );
    }
}
