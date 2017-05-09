<?php

namespace Kraken\Root\Provider;

use Kraken\Channel\Channel;
use Kraken\Config\Config;
use Kraken\Config\ConfigFactory;
use Kraken\Config\ConfigInterface;
use Kraken\Config\Overwrite\OverwriteMerger;
use Kraken\Config\Overwrite\OverwriteReverseIsolater;
use Kraken\Config\Overwrite\OverwriteReverseMerger;
use Kraken\Config\Overwrite\OverwriteReverseReplacer;
use Kraken\Container\ContainerInterface;
use Kraken\Runtime\RuntimeContextInterface;
use Kraken\Core\CoreInterface;
use Kraken\Container\ServiceProvider;
use Kraken\Container\ServiceProviderInterface;
use Kraken\Filesystem\Filesystem;
use Kraken\Filesystem\FilesystemAdapterFactory;
use Kraken\Runtime\Runtime;
use Kraken\Throwable\Exception\Runtime\ReadException;
use Kraken\Util\Support\ArraySupport;
use Kraken\Util\Support\StringSupport;

class ConfigProvider extends ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string[]
     */
    protected $requires = [
        'Kraken\Core\CoreInterface',
        'Kraken\Runtime\RuntimeContextInterface',
        'Kraken\Environment\EnvironmentInterface'
    ];

    /**
     * @var string[]
     */
    protected $provides = [
        'Kraken\Config\ConfigInterface'
    ];

    /**
     * @var CoreInterface
     */
    private $core;

    /**
     * @var RuntimeContextInterface
     */
    private $context;

    /**
     * @param ContainerInterface $container
     */
    protected function register(ContainerInterface $container)
    {
        $core    = $container->make('Kraken\Core\CoreInterface');
        $context = $container->make('Kraken\Runtime\RuntimeContextInterface');
        $env     = $container->make('Kraken\Environment\EnvironmentInterface');

        $this->core    = $core;
        $this->context = $context;

        $dir  = $this->getDir($context->getName(), $context->getType());
        $name = $context->getName();

        $prefix = $core->getDataPath() . '/config';
        $paths = [
            $prefix . '/' . $dir . '/' . $name,
            $prefix . '/Runtime/' . $name,
            $prefix . '/' . $dir,
            $prefix . '/Runtime'
        ];

        $path = '';
        $pathFound = false;

        foreach ($paths as $path)
        {
            if (is_dir($path))
            {
                $path .= '/config\.([a-zA-Z]*?)$';
                $pathFound = true;
                break;
            }
        }

        if (!$pathFound)
        {
            throw new ReadException('There is no valid configuration file.');
        }

        $data = $core->config();
        $data['imports'] = [];
        $data['imports'][] = [
            'resource' => $path,
            'mode'     => 'merge'
        ];

        $config = new Config($data);
        $config->setOverwriteHandler(new OverwriteReverseMerger);
        $this->configure($config);
        $config->setOverwriteHandler(new OverwriteMerger);

        $vars = array_merge(
            $config->exists('vars') ? $config->get('vars') : [],
            $this->getDefaultVariables()
        );

        $records = ArraySupport::flatten($config->getAll());
        foreach ($records as $key=>$value)
        {
            $new = $value;

            $new = preg_replace_callback(
                '#%env(\.([a-zA-Z0-9_-]*?))+%#si',
                function($matches) use($env) {
                    $key = strtoupper(str_replace([ '%', 'env.' ], [ '', '' ], $matches[0]));
                    return $env->getEnv($key);
                },
                $new);

            $new = preg_replace_callback(
                '#%func\.([a-zA-Z0-9_-]*?)%#si',
                function($matches) use($context) {
                    return call_user_func([ $context, $matches[1] ]);
                },
                $new);

            $new = StringSupport::parametrize($new, $vars);

            if (is_string($value) && $new != $value)
            {
                if (ctype_digit($new))
                {
                    $new = (int) $new;
                }

                $config->set($key, $new);
            }
        }

        $container->instance(
            'Kraken\Config\ConfigInterface',
            $config
        );
    }

    /**
     * @param ContainerInterface $container
     */
    protected function unregister(ContainerInterface $container)
    {
        unset($this->core);
        unset($this->context);

        $container->remove(
            'Kraken\Config\ConfigInterface'
        );
    }

    /**
     * @param string $path
     * @return ConfigInterface
     */
    private function createConfig($path)
    {
        $factory = new FilesystemAdapterFactory();

        $path = explode('/', $path);
        $file = array_pop($path);
        $path = implode('/', $path);

        return (new ConfigFactory(
            new Filesystem(
                $factory->create('Local', [ [ 'path' => $path ] ])
            ),
            [ '#' . $file . '#si' ]
        ))->create();
    }

    /**
     * @param string $name
     * @param string $type
     * @return string
     */
    private function getDir($name, $type)
    {
        if ($name === Runtime::RESERVED_CONSOLE_CLIENT || $name === Runtime::RESERVED_CONSOLE_SERVER)
        {
            return 'Console';
        }

        return $type;
    }

    /**
     * @param string $option
     * @return callable|null
     */
    private function getOverwriteHandler($option)
    {
        switch ($option)
        {
            case 'isolate':     return new OverwriteReverseIsolater();
            case 'replace':     return new OverwriteReverseReplacer();
            case 'merge':       return new OverwriteReverseMerger();
            default:            return null;
        }
    }

    /**
     * @param ConfigInterface $config
     */
    private function configure(ConfigInterface $config)
    {
        if ($config->exists('imports'))
        {
            $resources = (array) $config->get('imports');
        }
        else
        {
            $resources = [];
        }

        foreach ($resources as $resource)
        {
            $handler = isset($resource['mode'])
                ? $this->getOverwriteHandler($resource['mode'])
                : null
            ;

            $path = StringSupport::parametrize(
                $resource['resource'],
                $this->getDefaultVariables()
            );

            $current = $this->createConfig($path);

            $this->configure($current);

            $config->merge($current->getAll(), $handler);
        }
    }

    /**
     * @return string[]
     */
    private function getDefaultVariables()
    {
        $core    = $this->core;
        $context = $this->context;

        $vars = [
            'runtime'   => $context->getType(),
            'parent'    => $context->getParent() === null ? 'null' : $context->getParent(),
            'alias'     => $context->getAlias(),
            'name'      => $context->getName(),
            'basepath'  => $core->getBasePath(),
            'datapath'  => $core->getDataPath(),
            'localhost' => '127.0.0.1',
            'channel.binder'    => Channel::BINDER,
            'channel.connector' => Channel::CONNECTOR
        ];

        foreach ($context->getArgs() as $arg=>$val)
        {
            $vars['inherited.' . $arg] = $val;
        }

        return $vars;
    }
}
