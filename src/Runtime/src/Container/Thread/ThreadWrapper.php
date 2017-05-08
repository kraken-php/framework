<?php

namespace Kraken\Runtime\Container\Thread;

use Thread;

class ThreadWrapper extends Thread
{
    /**
     * @var ThreadController
     */
    public $controller;

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
     * @var string[]
     */
    public $args;

    /**
     * @param $controller
     * @param string $datapath
     * @param string $parent
     * @param string $alias
     * @param string $name
     * @param string[] $args
     */
    public function __construct($controller, $datapath, $parent, $alias, $name, $args = [])
    {
        $this->controller = $controller;
        $this->datapath = $datapath;
        $this->parent = $parent;
        $this->alias = $alias;
        $this->name = $name;
        $this->args = $args;
    }

    /**
     *
     */
    public function __destruct()
    {
//        unset($this->controller);
        unset($this->datapath);
        unset($this->parent);
        unset($this->alias);
        unset($this->name);
//        unset($this->args);
    }

    /**
     *
     */
    public function run()
    {
        $controller = $this->controller;
        $loader     = $this->controller->loader;

        $loader->register();

        $parent     = $this->parent;
        $alias      = $this->alias;
        $name       = $this->name;
        $args       = $this->args;

        require(
            $this->datapath . '/autorun/kraken.thread'
        );
    }

    /**
     * @return bool
     */
    public function kill()
    {
        if (!is_callable('parent::kill'))
        {
            return $this->controller->kill();
        }

        return parent::kill();
    }
}
