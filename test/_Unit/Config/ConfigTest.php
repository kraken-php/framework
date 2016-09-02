<?php

namespace Kraken\_Unit\Config;

use Kraken\Config\Config;
use Kraken\Config\Overwrite\OverwriteMerger;
use Kraken\Util\Support\ArraySupport;
use Kraken\Test\TUnit;

class ConfigTest extends TUnit
{
    /**
     *
     */
    public function testApiConstructor_SetsConfigAndHandler()
    {
        $handler = function() {};
        $config = $this->createConfig(null, $handler);

        $this->assertSame($this->getExpanded(), $config->get());
        $this->assertSame($handler, $config->getOverwriteHandler());
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowExceptions()
    {
        $config = $this->createConfig();
        unset($config);
    }

    /**
     *
     */
    public function testApiSetConfiguration_SetsConfiguration()
    {
        $config = $this->createConfig();
        $data = [
            'a'     => 'A',
            'c'     => [
                'x' => null,
                'y' => 0002
            ]
        ];

        $config->setConfiguration($data);

        $this->assertSame($data, $config->getConfiguration());
    }

    /**
     *
     */
    public function testApiGetConfiguration_GetsConfiguration()
    {
        $config = $this->createConfig();

        $this->assertSame($this->getExpanded(), $config->getConfiguration());
    }

    /**
     *
     */
    public function testApiSetOverwriteHandler_SetsOverwriteHandler()
    {
        $config = $this->createConfig();
        $handler = function() {};

        $config->setOverwriteHandler($handler);

        $this->assertSame($handler, $config->getOverwriteHandler());
    }

    /**
     *
     */
    public function testApiSetOverwriteHandler_SetsDefaultHandler_WhenNoHandlerPassed()
    {
        $config = $this->createConfig();

        $config->setOverwriteHandler();

        $this->assertTrue(is_callable($config->getOverwriteHandler()));
    }

    /**
     *
     */
    public function testApiGetOverwriteHandler_GetsOverwriteHandler()
    {
        $handler = function() {};
        $config = $this->createConfig(null, $handler);

        $this->assertSame($handler, $config->getOverwriteHandler());
    }

    /**
     *
     */
    public function testApiMerge_MergesConfigOnTopOfExistsing()
    {
        $config = $this->createConfig();
        $old = $config->getConfiguration();
        $new = [
            'b' => [ 'b' => 'new_Option' ],
            'h' => 'test',
            'a' => 5
        ];

        $config->merge($new);

        $merger = new OverwriteMerger();
        $this->assertSame(
            $merger($old, $new),
            $config->getConfiguration()
        );
    }

    /**
     *
     */
    public function testApiMerge_UsesHandler_WhenHandlerIsProvided()
    {
        $config = $this->createConfig();
        $old = $config->getConfiguration();
        $new = [
            'b' => [ 'b' => 'new_Option' ],
            'h' => 'test',
            'a' => 5
        ];

        $config->merge($new, function($old, $new) {
            return $old;
        });

        $this->assertSame(
            $old,
            $config->getConfiguration()
        );
    }

    /**
     *
     */
    public function testApiExists_ReturnsTrue_ForExistingKey()
    {
        $config = $this->createConfig();

        $this->assertTrue($config->exists('e.a'));
    }

    /**
     *
     */
    public function testApiExists_ReturnsFalse_ForNonExistingKey()
    {
        $config = $this->createConfig();

        $this->assertFalse($config->exists('null'));
    }

    /**
     *
     */
    public function testApiSet_SetsValue_WhenItExists()
    {
        $config = $this->createConfig();
        $key = 'e.a';
        $old = 0;
        $new = 'new';

        $this->assertSame($old, $config->get($key));
        $config->set($key, $new);

        $this->assertSame($new, $config->get($key));
    }

    /**
     *
     */
    public function testApiGet_ReturnsElement_WhenElementDoesExist()
    {
        $config = $this->createConfig();
        $key = 'e.a';
        $expected = 0;

        $this->assertSame($expected, $config->get($key));
    }

    /**
     *
     */
    public function testApiGet_ReturnsDefault_WhenElementDoesNotExist()
    {
        $config = $this->createConfig();
        $key = 'x.y.z';
        $default = 'XYZ';

        $this->assertSame($default, $config->get($key, $default));
    }

    /**
     *
     */
    public function testApiGet_ReturnsAllElements_WhenKeyIsEmptyString()
    {
        $config = $this->createConfig();

        $this->assertSame($config->getAll(), $config->get(''));
    }

    /**
     *
     */
    public function testApiRemove_RemovesElement_WhenElementDoesExist()
    {
        $config = $this->createConfig();
        $key = 'e.a';

        $this->assertTrue($config->exists($key));
        $config->remove($key);

        $this->assertFalse($config->exists($key));
    }

    /**
     *
     */
    public function testApiRemove_DoesNothing_WhenElementDoesNotExist()
    {
        $config = $this->createConfig();
        $key = 'x.y.z';

        $this->assertFalse($config->exists($key));
        $config->remove($key);

        $this->assertFalse($config->exists($key));
    }

    /**
     *
     */
    public function testApiAll_ReturnsWholeConfig()
    {
        $config = $this->createConfig();

        $this->assertSame($config->getConfiguration(), $config->getAll());
    }

    /**
     *
     */
    public function testApiGetDefaultHandler_ReturnsDefaultHandler()
    {
        $config = $this->createConfig();

        $handler = $this->callProtectedMethod($config, 'getDefaultHandler');

        $this->assertInstanceOf(OverwriteMerger::class, $handler);
    }

    /**
     *
     */
    public function testApiOverwrite_OverwritesConfig_UsingOverwriteHandler()
    {
        $handler = function($current, $new) {
            return array_merge($current, $new);
        };
        $config = $this->createConfig(null, $handler);

        $old = $config->getConfiguration();
        $new = [
            'b' => [ 'b' => 'new_Option' ],
            'h' => 'test',
            'a' => 5
        ];

        $merged = $this->callProtectedMethod($config, 'overwrite', [ $old, $new ]);

        $this->assertSame($handler($old, $new), $merged);
    }

    /**
     *
     */
    public function testApiOverwrite_OverwritesConfig_UsingPassedHandler()
    {
        $handler = function($current, $new) {
            return array_merge($current, $new);
        };
        $config = $this->createConfig();

        $old = $config->getConfiguration();
        $new = [
            'b' => [ 'b' => 'new_Option' ],
            'h' => 'test',
            'a' => 5
        ];

        $merged = $this->callProtectedMethod($config, 'overwrite', [ $old, $new, $handler ]);

        $this->assertSame($handler($old, $new), $merged);
    }

    /**
     * @return array
     */
    public function getExpanded()
    {
        return ArraySupport::expand($this->getRaw());
    }

    /**
     * @return array
     */
    public function getRaw()
    {
        return [
            'a' => null,
            'b' => [
                'a' => 5,
                'b' => [
                    'a' => 0002
                ],
                'c' => 'ABC'
            ],
            'c' => 'C',
            'd' => [
                'a' => 'TEST'
            ],
            'e.a' => 0,
            'e.b' => null
        ];
    }

    /**
     * @return Config
     */
    public function createConfig($config = null, callable $handler = null)
    {
        $config = $config === null ? $this->getRaw() : $config;

        return new Config($config, $handler);
    }
}
