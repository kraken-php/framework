<?php

namespace Kraken\Core\Provider\Config;

use Kraken\Config\Config;
use Kraken\Config\ConfigFactory;
use Kraken\Config\ConfigInterface;
use Kraken\Core\CoreInterface;
use Kraken\Core\CoreInputContextInterface;
use Kraken\Core\Service\ServiceProvider;
use Kraken\Core\Service\ServiceProviderInterface;
use Kraken\Filesystem\Filesystem;
use Kraken\Filesystem\FilesystemAdapterFactory;
use Kraken\Util\Support\ArraySupport;
use Kraken\Util\Support\StringSupport;

class ConfigProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Core\CoreInputContextInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Config\ConfigInterface'
    ];

    /**
     * @param CoreInterface $core
     */
    protected function register(CoreInterface $core)
    {
        $context = $core->make('Kraken\Core\CoreInputContextInterface');

        $global = $core->getDataPath() . '/config-global/' . $this->getDir($core->getType());
        $local  = $core->getDataPath() . '/config/' . $context->getName();

        $config = new Config();
        $this->addConfigByPath($config, $global);
        $this->addConfigByPath($config, $local);
        $this->addConfig($config, new Config($core->config()));

        $vars = array_merge(
            $config->exists('vars') ? $config->get('vars') : [],
            $this->getDefaultVariables($core, $context)
        );

        $records = ArraySupport::flatten($config->getAll());
        foreach ($records as $key=>$value)
        {
            $new = StringSupport::parametrize($value, $vars);
            if (is_string($value) && $new != $value)
            {
                $config->set($key, $new);
            }
        }

        $core->instance(
            'Kraken\Config\ConfigInterface',
            $config
        );
    }

    /**
     * @param CoreInterface $core
     */
    protected function unregister(CoreInterface $core)
    {
        $core->remove(
            'Kraken\Config\ConfigInterface'
        );
    }

    /**
     * @param string $path
     * @return ConfigInterface
     */
    private function createConfig($path)
    {
        if (!is_dir($path))
        {
            return new Config();
        }

        $factory = new FilesystemAdapterFactory();

        return (new ConfigFactory(
            new Filesystem(
                $factory->create('Local', [ [ 'path' => $path ] ])
            )
        ))->create();
    }

    /**
     * @param string $runtimeUnit
     * @return string
     */
    private function getDir($runtimeUnit)
    {
        return $runtimeUnit;
    }

    /**
     * @param string $option
     * @return callable
     */
    private function getOverwriteHandler($option)
    {
        switch ($option)
        {
            case 'isolate':     return Config::getOverwriteHandlerIsolater();
            case 'replace':     return Config::getOverwriteHandlerReplacer();
            case 'merge':       return Config::getOverwriteHandlerMerger();
            default:            return Config::getOverwriteHandlerMerger();
        }
    }

    /**
     * @param ConfigInterface $config
     * @param string $path
     */
    private function addConfigByPath(ConfigInterface $config, $path)
    {
        $this->addConfig($config, $this->createConfig($path));
    }

    /**
     * @param ConfigInterface $config
     * @param ConfigInterface $current
     */
    private function addConfig(ConfigInterface $config, ConfigInterface $current)
    {
        $dirs = (array) $current->get('config.dirs');
        foreach ($dirs as $dir)
        {
            $this->addConfigByPath($current, $dir);
        }

        if ($current->exists('config.mode'))
        {
            $config->setOverwriteHandler(
                $this->getOverwriteHandler($current->get('config.mode'))
            );
        }

        $config->merge($current->getAll());
    }

    /**
     * @param CoreInterface $core
     * @param CoreInputContextInterface $context
     * @return string[]
     */
    private function getDefaultVariables(CoreInterface $core, CoreInputContextInterface $context)
    {
        return [
            'runtime'   => $context->getType(),
            'parent'    => $context->getParent(),
            'alias'     => $context->getAlias(),
            'name'      => $context->getName(),
            'basepath'  => $core->getBasePath(),
            'datapath'  => $core->getDataPath(),
            'host.main' => '127.0.0.1'
        ];
    }
}
