<?php

namespace Kraken\_Module\Container;

use Kraken\_Unit\Container\_Asset\Bar;
use Kraken\_Unit\Container\_Asset\Bax;
use Kraken\_Unit\Container\_Asset\Baz;
use Kraken\_Unit\Container\_Asset\BazInterface;
use Kraken\_Unit\Container\_Asset\Foo;
use Kraken\_Unit\Container\_Asset\Invokable;
use Kraken\Container\Container;
use Dazzle\Throwable\Exception\Runtime\ReadException;
use Dazzle\Throwable\Exception\Runtime\WriteException;
use Kraken\Test\TModule;
use StdClass;

class ContainerTest extends TModule
{
    /**
     *
     */
    public function testApiExists_ReturnsFalse_WhenCustomMakeDefinitionIsNonExistingInterface()
    {
        $c = $this->createContainer();

        $this->assertFalse($c->exists(BazInterface::class));
    }

    /**
     *
     */
    public function testApiExists_ReturnsFalse_WhenCustomMakeDefinitionIsNotRegisteredAlias()
    {
        $c = $this->createContainer();
        $alias = 'alias';

        $this->assertFalse($c->exists($alias));
    }

    /**
     *
     */
    public function testApiExists_ReturnsTrue_WhenCustomMakeDefinitionIsClass()
    {
        $c = $this->createContainer();

        $this->assertTrue($c->exists(Baz::class));
    }

    /**
     *
     */
    public function testApiExists_ReturnsTrue_WhenCustomMakeDefinitionIsWired()
    {
        $c = $this->createContainer();
        $baz = new Baz;

        $c->wire(Bar::class, [ $baz ]);
        $this->assertTrue($c->exists(Bar::class));
    }

    /**
     *
     */
    public function testApiExists_ReturnsTrue_WhenCustomMakeDefinitionIsObject()
    {
        $c = $this->createContainer();
        $baz = new Baz;

        $c->bind(Baz::class, $baz);
        $this->assertTrue($c->exists(Baz::class));
    }

    /**
     *
     */
    public function testApiExists_ReturnsTrue_WhenCustomMakeDefinitionIsInterface()
    {
        $c = $this->createContainer();
        $baz = new Baz;

        $c->bind(BazInterface::class, $baz);
        $this->assertTrue($c->exists(BazInterface::class));
    }

    /**
     *
     */
    public function testApiExists_ReturnsTrue_WhenCustomMakeDefinitionIsSingleton()
    {
        $c = $this->createContainer();

        $c->share(Baz::class);
        $this->assertTrue($c->exists(Baz::class));
    }

    /**
     *
     */
    public function testApiExists_ReturnsTrue_WhenCustomMakeDefinitionIsParam()
    {
        $c = $this->createContainer();
        $alias = 'alias';
        $param = 'test';

        $this->assertFalse($c->exists($alias));
        $c->param($alias, $param);
        $this->assertTrue($c->exists($alias));
    }

    /**
     *
     */
    public function testApiExists_ReturnsTrue_WhenCustomMakeDefinitionIsFactoryMethod()
    {
        $c = $this->createContainer();
        $alias = 'alias';

        $this->assertFalse($c->exists($alias));
        $c->factory($alias, function() {});
        $this->assertTrue($c->exists($alias));
    }

    /**
     *
     */
    public function testApiWire_BindsDefaultParamsToMakeDefinition()
    {
        $c = $this->createContainer();
        $baz = new Baz;

        $foo = $c->make(Foo::class);
        $this->assertInstanceOf(Baz::class, $foo->baz);
        $this->assertNotSame($baz, $foo->baz);

        $c->wire(Foo::class, [ $baz ]);

        $foo = $c->make(Foo::class);
        $this->assertInstanceOf(Baz::class, $foo->baz);
        $this->assertSame($baz, $foo->baz);
    }

    /**
     *
     */
    public function testApiWire_ThrowsException_WhenNonInitializableOrNonExistingClassPassed()
    {
        $c = $this->createContainer();

        $this->setExpectedException(WriteException::class);
        $c->wire(BazInterface::class, []);
    }

    /**
     *
     */
    public function testApiBind_BindsToObjectDefinition()
    {
        $c = $this->createContainer();
        $baz = new Baz;

        $make = $c->make(Baz::class);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertNotSame($baz, $make);

        $c->bind(Baz::class, $baz);

        $make = $c->make(Baz::class);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertSame($baz, $make);
    }

    /**
     *
     */
    public function testApiBind_BindsToInterfaceDefinition()
    {
        $c = $this->createContainer();
        $baz = new Baz;

        $c->bind(BazInterface::class, $baz);

        $make = $c->make(BazInterface::class);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertInstanceOf(BazInterface::class, $make);
        $this->assertSame($baz, $make);
    }

    /**
     *
     */
    public function testApiBind_BindsToAlias()
    {
        $c = $this->createContainer();
        $baz = new Baz;
        $alias = 'alias';

        $c->bind($alias, $baz);

        $make = $c->make($alias);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertInstanceOf(BazInterface::class, $make);
        $this->assertSame($baz, $make);
    }

    /**
     *
     */
    public function testApiBind_BindsObject()
    {
        $c = $this->createContainer();
        $baz = new Baz;

        $c->bind(Baz::class, $baz);

        $make = $c->make(Baz::class);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertInstanceOf(BazInterface::class, $make);
        $this->assertSame($baz, $make);
    }

    /**
     *
     */
    public function testApiBind_BindsClass()
    {
        $c = $this->createContainer();

        $c->bind(BazInterface::class, Baz::class);

        $make = $c->make(BazInterface::class);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertInstanceOf(BazInterface::class, $make);
    }

    /**
     *
     */
    public function testApiBind_BindsPrimitive()
    {
        $c = $this->createContainer();
        $alias = 'alias';
        $param = 'param';

        $c->bind($alias, $param);

        $make = $c->make($alias);
        $this->assertSame($param, $make);
    }

    /**
     *
     */
    public function testApiBind_BindsFactoryMethod()
    {
        $c = $this->createContainer();
        $baz = new Baz;

        $c->bind(Baz::class, function() use($baz) {
            return $baz;
        });

        $make = $c->make(Baz::class);
        $this->assertSame($baz, $make);
    }

    /**
     *
     */
    public function testApiBind_ThrowsException_WhenInvalidReferenceSet()
    {
        $c = $this->createContainer();
        $alias = 'alias';

        $this->setExpectedException(WriteException::class);
        $c->bind(Bar::class, $alias);
    }

    /**
     *
     */
    public function testApiBind_BindsInvokableObjectAsObjectNotCallable()
    {
        $c = $this->createContainer();

        $c->bind(Invokable::class, $invokable = new Invokable);

        $this->assertInstanceOf(Invokable::class, $make = $c->make(Invokable::class));
        $this->assertSame('undefined', $make->name);
        $this->assertSame([], $make->args);
    }

    /**
     *
     */
    public function testApiAlias_CreatesAliasToOtherDefinition()
    {
        $c = $this->createContainer();
        $alias = 'alias';

        $c->alias($alias, Baz::class);
        $this->assertInstanceOf(Baz::class, $c->make($alias));
    }

    /**
     *
     */
    public function testApiAlias_ThrowsException_WhenOtherDefinitionDoesNotExist()
    {
        $c = $this->createContainer();
        $alias = 'alias';
        $other = 'other';

        $this->setExpectedException(WriteException::class);
        $c->alias($alias, $other);
    }

    /**
     *
     */
    public function testApiAlias_RespectsInstantiationRestrictionsOfOtherDefinition()
    {
        $this->markTestSkipped('Currently alias() method does not support this behaviour, but it should, check task KRF-28');
    }

    /**
     *
     */
    public function testApiInstance_BindsObject_ToClassDefinition()
    {
        $c = $this->createContainer();
        $baz = new Baz;

        $make = $c->make(Baz::class);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertNotSame($baz, $make);

        $c->instance(Baz::class, $baz);

        $make = $c->make(Baz::class);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertSame($baz, $make);
    }

    /**
     *
     */
    public function testApiInstance_BindsObject_ToInterfaceDefinition()
    {
        $c = $this->createContainer();
        $baz = new Baz;

        $c->instance(BazInterface::class, $baz);

        $make = $c->make(BazInterface::class);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertInstanceOf(BazInterface::class, $make);
        $this->assertSame($baz, $make);
    }

    /**
     *
     */
    public function testApiInstance_BindsObject_ToAliasDefinition()
    {
        $c = $this->createContainer();
        $baz = new Baz;
        $alias = 'alias';

        $c->instance($alias, $baz);

        $make = $c->make($alias);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertInstanceOf(BazInterface::class, $make);
        $this->assertSame($baz, $make);
    }

    /**
     *
     */
    public function testApiInstance_ThowsException_WhenTriedToBindClass()
    {
        $c = $this->createContainer();

        $this->setExpectedException(WriteException::class);
        $c->instance(BazInterface::class, Baz::class);
    }

    /**
     *
     */
    public function testApiInstance_ThrowsException_WhenTriedToBindPrimitive()
    {
        $c = $this->createContainer();
        $alias = 'alias';
        $param = 'param';

        $this->setExpectedException(WriteException::class);
        $c->instance($alias, $param);
    }

    /**
     *
     */
    public function testApiInstance_ThrowsException_WhenTriedToBindFactoryMethod()
    {
        $c = $this->createContainer();

        $this->setExpectedException(WriteException::class);
        $c->instance(Baz::class, function() {});
    }

    /**
     *
     */
    public function testApiInstance_BindsInvokableObjectAsObjectNotCallable()
    {
        $c = $this->createContainer();

        $c->instance(Invokable::class, $invokable = new Invokable);

        $this->assertInstanceOf(Invokable::class, $make = $c->make(Invokable::class));
        $this->assertSame('undefined', $make->name);
        $this->assertSame([], $make->args);
    }

    /**
     *
     */
    public function testApiShare_SetsDefinitionAsSingleton()
    {
        $c = $this->createContainer();

        $this->assertNotSame($c->make(Baz::class), $c->make(Baz::class));
        $c->share(Baz::class);
        $this->assertSame($c->make(Baz::class), $c->make(Baz::class));
    }

    /**
     *
     */
    public function testApiShare_SetsDefinitionAsSingleton_WithDefaultParams()
    {
        $c = $this->createContainer();

        $make1 = $c->make(Foo::class);
        $make2 = $c->make(Foo::class);
        $this->assertNotSame($make1, $make2);

        $c->share(Foo::class, [ $baz = new Baz ]);

        $make1 = $c->make(Foo::class);
        $make2 = $c->make(Foo::class);
        $this->assertSame($make1, $make2);

        $this->assertSame($baz, $make1->baz);
        $this->assertSame($baz, $make2->baz);
    }

    /**
     *
     */
    public function testApiShare_ThrowsException_WhenTriedToSetObject()
    {
        $c = $this->createContainer();
        $foo = new Foo(new Baz);

        $this->setExpectedException(WriteException::class);
        $c->share(Foo::class, $foo);
    }

    /**
     *
     */
    public function testApiShare_SetsPrimitiveAsSingleton()
    {
        $c = $this->createContainer();
        $alias = 'alias';
        $param = 'param';

        $this->setExpectedException(WriteException::class);
        $c->share($alias, $param);
    }

    /**
     *
     */
    public function testApiShare_SetsFactoryMethodAsSingleton()
    {
        $c = $this->createContainer();

        $this->setExpectedException(WriteException::class);
        $c->share(Baz::class, function() {
            return new Baz;
        });
    }

    /**
     *
     */
    public function testApiShare_ThrowsException_WhenInvalidReferenceSet()
    {
        $c = $this->createContainer();

        $this->setExpectedException(WriteException::class);
        $c->share(BazInterface::class);
    }

    /**
     *
     */
    public function testApiParam_SetsParam_WhenParamIsPrimitive()
    {
        $c = $this->createContainer();
        $alias = 'alias';
        $param = 'text';

        $c->param($alias, $param);
        $this->assertSame($param, $c->make($alias));
    }

    /**
     *
     */
    public function testApiParam_ThrowsException_WhenParamIsObject()
    {
        $c = $this->createContainer();
        $alias = 'alias';

        $this->setExpectedException(WriteException::class);
        $c->param($alias, new Baz);
    }

    /**
     *
     */
    public function testApiParam_ThrowsException_WhenParamIsCallable()
    {
        $c = $this->createContainer();
        $alias = 'alias';

        $this->setExpectedException(WriteException::class);
        $c->param($alias, function() {});
    }

    /**
     *
     */
    public function testApiParam_ThrowsException_WhenAliasIsClass()
    {
        $c = $this->createContainer();
        $param = 'param';

        $this->setExpectedException(WriteException::class);
        $c->param(Baz::class, $param);
    }

    /**
     *
     */
    public function testApiFactory_RegistersFactoryMethod_WithoutDefaultParameters()
    {
        $c = $this->createContainer();
        $alias = 'alias';
        $even = new Baz;
        $odd  = new Baz;

        $c->factory($alias, function($val) use($even, $odd) {
            return $val % 2 === 0 ? $even : $odd;
        });

        $this->assertSame($even, $c->make($alias, [ 0 ]));
        $this->assertSame($odd,  $c->make($alias, [ 1 ]));
    }

    /**
     *
     */
    public function testApiFactory_RegistersFactoryMethod_WithDefaultParameters()
    {
        $c = $this->createContainer();
        $alias = 'alias';
        $even = new Baz;
        $odd  = new Baz;

        $c->factory($alias, function($val) use($even, $odd) {
            return $val % 2 === 0 ? $even : $odd;
        }, [
            1
        ]);

        $this->assertSame($even, $c->make($alias, [ 0 ]));
        $this->assertSame($odd,  $c->make($alias, [ 1 ]));
        $this->assertSame($odd,  $c->make($alias));
    }

    /**
     *
     */
    public function testApiFactory_FiresFactoryMethodEachTime()
    {
        $c = $this->createContainer();
        $alias = 'alias';

        $c->factory($alias, function() {
            return new Baz;
        });

        $this->assertNotSame($c->make($alias), $c->make($alias));
    }

    /**
     *
     */
    public function testApiFactory_BindsInvokableObjectAsCallable()
    {
        $this->markTestSkipped('Currently factory() method does not support this behaviour, but it should, check task KRF-269');

        $c = $this->createContainer();
        $alias = 'alias';

        $name = 'name';
        $args = [ 'arg1', 'arg2' ];

        $c->factory($alias, $invokable = new Invokable);
        $make = $c->make($alias, [ $name, $args ]);

        $this->assertInstanceOf(Baz::class, $make);
        $this->assertSame($name, $invokable->name);
        $this->assertSame($args, $invokable->args);
    }

    /**
     *
     */
    public function testApiMake_GetsParam()
    {
        $c = $this->createContainer();
        $alias = 'alias';
        $param = 'param';

        $c->param($alias, $param);

        $this->assertSame($param, $c->make($alias));
    }

    /**
     *
     */
    public function testApiMake_MakesAliasedDefinition()
    {
        $c = $this->createContainer();
        $alias = 'alias';

        $c->alias($alias, Baz::class);

        $this->assertInstanceOf(Baz::class, $c->make($alias));
    }

    /**
     *
     */
    public function testApiMake_MakesClass()
    {
        $c = $this->createContainer();

        $this->assertInstanceOf(Baz::class, $c->make(Baz::class));
    }

    /**
     *
     */
    public function testApiMake_GetsObject()
    {
        $c = $this->createContainer();

        $c->bind(Baz::class, $baz = new Baz);

        $this->assertSame($baz, $c->make(Baz::class));
    }

    /**
     *
     */
    public function testApiMake_InvokesFactoryMethod()
    {
        $c = $this->createContainer();
        $baz = new Baz;

        $c->factory(Baz::class, function() use($baz) {
            return $baz;
        });

        $this->assertSame($baz, $c->make(Baz::class));
    }

    /**
     *
     */
    public function testApiMake_SupportsFullAutowiring()
    {
        $c = $this->createContainer();
        $c->alias(BazInterface::class, Baz::class);

        $make = $c->make(Bax::class);

        $this->assertInstanceOf(Bax::class, $make);
        $this->assertInstanceOf(Baz::class, $make->baz);
        $this->assertInstanceOf(Bar::class, $make->bar);
        $this->assertInstanceOf(Baz::class, $make->bar->baz);
    }

    /**
     *
     */
    public function testApiMake_SupportsPartialAutowiring()
    {
        $c = $this->createContainer();

        $c->bind(Baz::class, $baz = new Baz);
        $c->alias(BazInterface::class, Baz::class);

        $make = $c->make(Bax::class);

        $this->assertInstanceOf(Bax::class, $make);
        $this->assertInstanceOf(Baz::class, $make->baz);
        $this->assertInstanceOf(Bar::class, $make->bar);
        $this->assertInstanceOf(Baz::class, $make->bar->baz);

        $this->assertSame($baz, $make->baz);
        $this->assertSame($baz, $make->bar->baz);
    }

    /**
     *
     */
    public function testApiMake_TreatsInvokableAsObjectNotCallable()
    {
        $c = $this->createContainer();

        $this->assertInstanceOf(Invokable::class, $c->make(Invokable::class));
    }

    /**
     *
     */
    public function testApiRemove_RemovesDefinition_WhenDefinitionPointsToPrimitive()
    {
        $c = $this->createContainer();
        $alias = 'alias';
        $param = 'param';

        $c->param($alias, $param);
        $this->assertTrue($c->exists($alias));

        $c->remove($alias);
        $this->assertFalse($c->exists($alias));
    }

    /**
     *
     */
    public function testApiRemove_RemovesDefinition_WhenDefinitionPointsToObject()
    {
        $c = $this->createContainer();
        $baz = new Baz;

        $c->bind(Baz::class, $baz);
        $make = $c->make(Baz::class);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertSame($baz, $make);

        $c->remove(Baz::class);
        $make = $c->make(Baz::class);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertNotSame($baz, $make);
    }

    /**
     *
     */
    public function testApiRemove_RemovesDefinition_WhenDefinitionPointsToFactoryMethod()
    {
        $c = $this->createContainer();
        $baz = new Baz;

        $c->factory(Baz::class, function() use($baz) {
            return $baz;
        });
        $make = $c->make(Baz::class);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertSame($baz, $make);

        $c->remove(Baz::class);
        $make = $c->make(Baz::class);
        $this->assertInstanceOf(Baz::class, $make);
        $this->assertNotSame($baz, $make);
    }

    /**
     *
     */
    public function testApiRemove_DoesNothing_WhenPassedNonExistingDefinition()
    {
        $c = $this->createContainer();
        $alias = 'alias';

        $c->remove($alias);
    }

    /**
     *
     */
    public function testApiCall_ShouldCallMethod_WithAutowiredDependencies()
    {
        $c = $this->createContainer();

        $std = $c->call(function(Baz $baz, Foo $foo) {
            $std = new StdClass;
            $std->baz = $baz;
            $std->foo = $foo;
            return $std;
        });

        $this->assertInstanceOf(StdClass::class, $std);
        $this->assertInstanceOf(Baz::class, $std->baz);
        $this->assertInstanceOf(Foo::class, $std->foo);
        $this->assertInstanceOf(Baz::class, $std->foo->baz);
    }

    /**
     *
     */
    public function testApiCall_ShouldCallMethod_WithInjectedDependencies()
    {
        $c = $this->createContainer();

        $std = $c->call(function(Baz $baz, Foo $foo) {
            $std = new StdClass;
            $std->baz = $baz;
            $std->foo = $foo;
            return $std;
        }, [
            $baz = new Baz,
            $foo = new Foo($baz)
        ]);

        $this->assertInstanceOf(StdClass::class, $std);
        $this->assertSame($baz, $std->baz);
        $this->assertSame($foo, $std->foo);
        $this->assertSame($baz, $std->foo->baz);
    }


    /**
     *
     */
    public function testApiCall_ShouldCallMethod_WithDefaultDependencies()
    {
        $c = $this->createContainer();

        $c->bind(Baz::class, $baz = new Baz);
        $c->bind(Foo::class, $foo = new Foo($baz));

        $std = $c->call(function(Baz $baz, Foo $foo) {
            $std = new StdClass;
            $std->baz = $baz;
            $std->foo = $foo;
            return $std;
        });

        $this->assertInstanceOf(StdClass::class, $std);
        $this->assertSame($baz, $std->baz);
        $this->assertSame($foo, $std->foo);
        $this->assertSame($baz, $std->foo->baz);
    }

    /**
     *
     */
    public function testApiCall_ThrowsException_WhenDependenciesCouldNotBeResolvedBecauseOfNotInitializableClass()
    {
        $c = $this->createContainer();

        $this->setExpectedException(ReadException::class);
        $c->call(function(BazInterface $baz) {});
    }

    /**
     *
     */
    public function testApiCall_ThrowsException_WhenDependenciesCouldNotBeResolvedBecauseOfLackOfHint()
    {
        $c = $this->createContainer();

        $this->setExpectedException(ReadException::class);
        $c->call(function($baz) {});
    }

    /**
     *
     */
    public function testApiCall_ThrowsException_WhenDefinitionAndCallParametersDoesNotMatch()
    {
        $c = $this->createContainer();

        $this->setExpectedException(ReadException::class);
        $c->call(function(BazInterface $baz) {}, [ new Foo(new Baz) ]);
    }

    /**
     * @return Container
     */
    public function createContainer()
    {
        return new Container();
    }
}
