<?php

namespace Kraken\Filesystem;

use League\Flysystem\AdapterInterface;
use Kraken\Util\Factory\SimpleFactory;
use Kraken\Util\Factory\SimpleFactoryInterface;

abstract class FilesystemAdapterSimpleFactory extends SimpleFactory implements SimpleFactoryInterface
{
    /**
     * @var mixed[]
     */
    protected $defaults;

    /**
     * @param mixed[] $defaults
     */
    public function __construct($defaults = [])
    {
        parent::__construct();

        $this->defaults = array_merge(
            $this->getDefaults(),
            $defaults
        );

        $this->define([ $this, 'onCreate' ]);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->defaults);

        parent::__destruct();
    }

    /**
     * Return array with default settings that needs to be passed to adapter.
     *
     * @return mixed[]
     */
    abstract protected function getDefaults();

    /**
     * Return client class for adapter creation if it needs one.
     *
     * @return string
     */
    abstract protected function getClient();

    /**
     * Return class of adapter.
     *
     * @return string
     */
    abstract protected function getClass();

    /**
     * Factory method for adapter.
     *
     * @param mixed[] $config
     * @return AdapterInterface
     */
    abstract protected function onCreate($config = []);

    /**
     * Return param saved under specified key in local settings or fallbacks to global settings.
     *
     * @param mixed[] $local
     * @param string $name
     * @return mixed|null
     */
    protected function param($local, $name)
    {
        return isset($local[$name]) ? $local[$name] : (isset($this->defaults[$name]) ? $this->defaults[$name] : null);
    }

    /**
     * Return given local settings merged onto global ones.
     *
     * @param mixed[] $local
     * @return mixed[]
     */
    protected function params($local = [])
    {
        return array_merge($this->defaults, $local);
    }
}
