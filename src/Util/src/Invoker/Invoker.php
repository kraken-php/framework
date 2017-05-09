<?php

namespace Kraken\Util\Invoker;

class Invoker implements InvokerInterface
{
    /**
     * @var callable[]
     */
    protected $proxies;

    /**
     * @param callable[] $proxies
     */
    public function __construct($proxies = [])
    {
        $this->proxies = [];

        foreach ($proxies as $func=>$proxy)
        {
            $this->setProxy($func, $proxy);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->proxies);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function call($func, $args = [])
    {
        return call_user_func_array($this->getProxy($func), $args);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsProxy($func)
    {
        return isset($this->proxies[$func]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setProxy($func, $callable)
    {
        $this->proxies[$func] = $callable;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeProxy($func)
    {
        unset($this->proxies[$func]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getProxy($func)
    {
        return isset($this->proxies[$func]) ? $this->proxies[$func] : $func;
    }
}
