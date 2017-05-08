<?php

namespace Kraken\Environment\Loader;

use Kraken\Util\Invoker\Invoker;
use Kraken\Util\Invoker\InvokerInterface;

class Loader extends \Dotenv\Loader
{
    /**
     * @var InvokerInterface
     */
    protected $invoker;

    /**
     * @param string $filePath
     * @param bool $immutable
     */
    public function __construct($filePath, $immutable = false)
    {
        parent::__construct($filePath, $immutable);

        $this->invoker = $this->createInvoker();
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->invoker);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setEnvironmentVariable($name, $value = null)
    {
        if (strpos($name, 'INI_') === 0)
        {
            list($name, $value) = $this->normaliseEnvironmentVariable($name, $value);

            $normalized = str_replace('INI_', '', $name);
            $normalized = strtolower($normalized);

            $this->invoker->call('ini_set', [ $normalized, $value ]);
        }

        parent::setEnvironmentVariable($name, $value);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function clearEnvironmentVariable($name)
    {
        if (strpos($name, 'INI_') === 0)
        {
            $normalized = str_replace('INI_', '', $name);
            $normalized = strtolower($normalized);

            $this->invoker->call('ini_restore', [ $normalized ]);
        }

        parent::clearEnvironmentVariable($name);
    }

    /**
     * @return InvokerInterface
     */
    protected function createInvoker()
    {
        return new Invoker();
    }
}
