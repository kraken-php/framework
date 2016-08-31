<?php

namespace Kraken\_Unit\Environment\_Mock;

use Kraken\Environment\Environment;
use Kraken\Util\Invoker\Invoker;
use Kraken\Util\Invoker\InvokerInterface;

class EnvironmentMock extends Environment
{
    /**
     * @var
     */
    public $calls = [];

    /**
     * @return InvokerInterface
     */
    protected function createInvoker()
    {
        return new Invoker([
            'ini_set' => function($key, $val) {
                $this->addCall('ini_set', [ $key, $val ]);
                return;
            },
            'ini_get' => function($key) {
                $this->addCall('ini_get', [ $key ]);
                return $key;
            },
            'ini_restore' => function($key) {
                $this->addCall('ini_restore', [ $key ]);
                return $key;
            },
            'set_error_handler' => function($handler) {
                $this->addCall('set_error_handler', [ $handler ]);
                return;
            },
            'register_shutdown_function' => function($handler) {
                $this->addCall('register_shutdown_function', [ $handler ]);
                return;
            },
            'set_exception_handler' => function($handler) {
                $this->addCall('set_exception_handler', [ $handler ]);
                return;
            },
            'pcntl_signal' => function($signal, $handler) {
                $this->addCall('pcntl_signal', [ $signal, $handler ]);
                return;
            }
        ]);
    }

    /**
     * @param string $name
     * @param mixed $args
     */
    private function addCall($name, $args = [])
    {
        $this->calls[$name] = $args;
    }
}
