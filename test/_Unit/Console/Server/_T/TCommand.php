<?php

namespace Kraken\_Unit\Console\Server\_T;

use Kraken\Channel\Channel;
use Kraken\Channel\ChannelInterface;
use Kraken\Console\Server\Manager\ProjectManager;
use Kraken\Console\Server\Manager\ProjectManagerInterface;
use Kraken\Runtime\Command\CommandInterface;
use Kraken\Config\Config;
use Kraken\Config\ConfigInterface;
use Kraken\Core\Core;
use Kraken\Core\CoreInterface;
use Kraken\Runtime\Command\Command;
use Kraken\Runtime\RuntimeContainer;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Runtime\RuntimeManagerInterface;
use Kraken\Supervision\SupervisorInterface;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Test\TUnit;
use Exception;

class TCommand extends TUnit
{
    /**
     * @var string
     */
    protected $class = '';

    /**
     * @var CommandInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cmd;

    /**
     * @var RuntimeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $manager;

    /**
     * @var ProjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $project;

    /**
     * @var RuntimeContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $runtime;

    /**
     * @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $core;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->cmd = null;
        $this->manager = null;
        $this->runtime = null;
    }

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $command = $this->createCommand();

        $this->assertInstanceOf(Command::class, $command);
        $this->assertInstanceOf(CommandInterface::class, $command);
    }

    /**
     *
     */
    public function testApiConstructor_SetsRuntimeInContext()
    {
        $command = $this->createCommand();
        $context = $this->getProtectedProperty($command, 'context');

        $this->assertInstanceOf(RuntimeContainerInterface::class, $context['runtime']);
    }

    /**
     *
     */
    public function testApiConstructor_ThrowsException_WhenNoRuntimeIsPassed()
    {
        $this->setExpectedException(InstantiationException::class);
        $this->createCommand([ 'runtime' => null ]);
    }

    /**
     *
     */
    public function testApiConstruct_DoesNotThrowException()
    {
        $command = $this->createCommand();

        $this->callProtectedMethod($command, 'construct');
    }

    /**
     *
     */
    public function testApiDestructor_DoesNotThrowException()
    {
        $command = $this->createCommand();
        unset($command);
    }

    /**
     *
     */
    public function testApiDestruct_DoesNotThrowException()
    {
        $command = $this->createCommand();

        $this->callProtectedMethod($command, 'destruct');
    }

    /**
     * @param string[]|null $data
     * @param string[]|null $methods
     * @return ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createConfig($data = [], $methods = null)
    {
        $config = $this->getMock(Config::class, $methods, [ $data ]);

        if ($this->cmd !== null && $this->existsProtectedProperty($this->cmd, 'config'))
        {
            $this->setProtectedProperty($this->cmd, 'config', $config);
        }

        return $config;
    }

    /**
     * @param string[]|null $methods
     * @return ChannelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createChannel($methods = [])
    {
        $channel = $this->getMock(Channel::class, $methods, [], '', false);

        if ($this->cmd !== null && $this->existsProtectedProperty($this->cmd, 'channel'))
        {
            $this->setProtectedProperty($this->cmd, 'channel', $channel);
        }

        return $channel;
    }

    /**
     * @param string[]|null $methods
     * @return SupervisorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createSupervisor($methods = [])
    {
        $super = $this->getMock(SupervisorInterface::class, $methods, [], '', false);

        if ($this->cmd !== null && $this->existsProtectedProperty($this->cmd, 'supervisor'))
        {
            $this->setProtectedProperty($this->cmd, 'supervisor', $super);
        }

        return $super;
    }

    /**
     * @param string[]|null $methods
     * @return ProjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createProjectManager($methods = [])
    {
        $project = $this->getMock(ProjectManager::class, $methods, [], '', false);

        if ($this->cmd !== null && $this->existsProtectedProperty($this->cmd, 'manager'))
        {
            $this->setProtectedProperty($this->cmd, 'manager', $project);
        }

        return $project;
    }

    /**
     * @return RuntimeManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createManager()
    {
        if ($this->cmd !== null && $this->existsProtectedProperty($this->cmd, 'manager'))
        {
            $this->setProtectedProperty($this->cmd, 'manager', $this->manager);
        }

        return $this->manager;
    }

    /**
     * @param string[]|null $methods
     * @return RuntimeContainerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createRuntime($methods = [])
    {
        $methods = array_merge($methods, [
            'getManager',
            'getCore'
        ]);

        $manager = $this->getMock(RuntimeManagerInterface::class, [], [], '', false);
        $core    = $this->getMock(Core::class, [ 'make' ], []);
        $core
            ->expects($this->any())
            ->method('make')
            ->will($this->returnValue(null));

        $runtime = $this->getMock(RuntimeContainer::class, $methods, [ 'parent', 'alias', 'name' ]);
        $runtime
            ->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($manager));
        $runtime
            ->expects($this->any())
            ->method('getCore')
            ->will($this->returnValue($core));

        if ($this->cmd !== null)
        {
            $this->setProtectedProperty($this->cmd, 'runtime', $runtime);
        }

        $this->manager = $manager;
        $this->runtime = $runtime;
        $this->core    = $core;

        return $runtime;
    }

    /**
     * @param array $context
     * @param array $methods
     * @return Command|\PHPUnit_Framework_MockObject_MockObject
     * @throws Exception
     */
    public function createCommand($context = [], $methods = [])
    {
        if ($this->class === '')
        {
            throw new Exception('Class not set');
        }

        if (!array_key_exists('runtime', $context))
        {
            $context['runtime'] = $this->createRuntime();
        }

        $this->cmd = $this->getMock($this->class, $methods, [ $context ]);

        return $this->cmd;
    }
}
