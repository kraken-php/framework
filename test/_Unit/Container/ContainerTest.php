<?php

namespace Kraken\_Unit\Container;

use Kraken\_Unit\Container\_Asset\Baz;
use Kraken\_Unit\Container\_Asset\BazInterface;
use Kraken\_Unit\Container\_Mock\ContainerMock;
use Kraken\Container\Container;
use Kraken\Container\Model\ContainerModel;
use Kraken\Container\Model\ContainerReflection;
use Kraken\Test\TUnit;
use Dazzle\Throwable\Exception\Runtime\ReadException;
use Dazzle\Throwable\Exception\Runtime\WriteException;
use Prophecy\Argument;
use Exception;

class ContainerTest extends TUnit
{
    /**
     * @var ContainerModel
     */
    private $container;

    /**
     * @var ContainerReflection
     */
    private $reflector;

    /**
     *
     */
    public function testApiConstructor_DoesNotThrowException()
    {
        $this->createContainer();
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $container = $this->createContainer();
        unset($container);
    }

    /**
     *
     */
    public function testApiWire_ThrowsException_WhenModelThrowsException()
    {
        $this->setExpectedException(WriteException::class);

        $container = $this->createContainer();

        $this->container->add(Baz::class)->willThrow(new Exception())->shouldBeCalledTimes(1);
        $container->wire(Baz::class, []);
    }

    /**
     *
     */
    public function testApiShare_ThrowsException_WhenModelThrowsException()
    {
        $this->setExpectedException(WriteException::class);

        $container = $this->createContainer();

        $this->container->add(Baz::class, null, true)->willThrow(new Exception())->shouldBeCalledTimes(1);
        $container->share(Baz::class, []);
    }

    /**
     *
     */
    public function testApiBind_ThrowsException_WhenModelThrowsException()
    {
        $this->setExpectedException(WriteException::class);

        $container = $this->createContainer();
        $baz = new Baz;

        $this->container->add(Baz::class, $baz, true)->willThrow(new Exception())->shouldBeCalledTimes(1);
        $container->bind(Baz::class, $baz);
    }

    /**
     *
     */
    public function testApiAlias_ThrowsException_WhenModelThrowsException()
    {
        $this->setExpectedException(WriteException::class);

        $container = $this->createContainer();

        $this->container->get(Baz::class)->willThrow(new Exception())->shouldBeCalledTimes(1);
        $container->alias(BazInterface::class, Baz::class);
    }

    /**
     *
     */
    public function testApiParam_ThrowsException_WhenModelThrowsException()
    {
        $this->setExpectedException(WriteException::class);

        $container = $this->createContainer();
        $alias = 'alias';
        $param = 'param';

        $this->container->add($alias, $param)->willThrow(new Exception())->shouldBeCalledTimes(1);
        $container->param($alias, $param);
    }

    /**
     *
     */
    public function testApiFactory_ThrowsException_WhenModelThrowsException()
    {
        $this->setExpectedException(WriteException::class);

        $container = $this->createContainer();
        $alias = 'alias';
        $callback = function() {};

        $this->container->add($alias, $callback)->willThrow(new Exception())->shouldBeCalledTimes(1);
        $container->factory($alias, $callback);
    }

    /**
     *
     */
    public function testApiMake_ThrowsException_WhenModelThrowsException()
    {
        $this->setExpectedException(ReadException::class);

        $container = $this->createContainer();
        $alias = 'alias';
        $args = [];

        $this->container->get($alias, $args)->willThrow(new Exception())->shouldBeCalledTimes(1);
        $container->make($alias, $args);
    }

    /**
     *
     */
    public function testApiCall_ThrowsException_WhenModelThrowsException()
    {
        $this->setExpectedException(ReadException::class);

        $container = $this->createContainer();
        $callback = function() {};
        $args = [];

        $this->reflector->reflectArguments(Argument::cetera())->willThrow(new Exception())->shouldBeCalledTimes(1);
        $container->call($callback, $args);
    }

    /**
     * @return ContainerMock|Container
     */
    public function createContainer()
    {
        $this->container = $this->prophesize(ContainerModel::class);
        $this->reflector = $this->prophesize(ContainerReflection::class);

        return new ContainerMock(
            $this->container->reveal(),
            $this->reflector->reveal()
        );
    }
}
