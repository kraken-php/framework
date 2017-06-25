<?php

namespace Kraken\_Unit\Console\Client\_T;

use Dazzle\Channel\ChannelInterface;
use Kraken\Runtime\Command\CommandInterface;
use Kraken\Console\Client\Command\Command;
use Kraken\Test\TUnit;
use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->cmd = null;
    }

    /**
     *
     */
    public function testApiConstructor_CreatesInstance()
    {
        $command = $this->createCommand();

        $this->assertInstanceOf(Command::class, $command);
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
     * @param Command $command
     * @param string $name
     * @param string $desc
     * @param mixed[] $args
     * @param mixed[] $opts
     */
    public function assertCommand(Command $command, $name, $desc = "#(.*?)#si", $args = [], $opts = [])
    {
        $this->assertSame($name, $command->getName());
        $this->assertRegExp($desc, $command->getDescription());

        $def = $command->getDefinition();

        foreach ($args as $arg)
        {
            $this->assertTrue($def->hasArgument($arg[0]));

            if ($arg[1] === InputArgument::REQUIRED)
            {
                $this->assertTrue($def->getArgument($arg[0])->isRequired());
            }
            if ($arg[1] === InputArgument::OPTIONAL)
            {
                $this->assertFalse($def->getArgument($arg[0])->isRequired());
            }
            if ($arg[1] === InputArgument::IS_ARRAY)
            {
                $this->assertTrue($def->getArgument($arg[0])->isArray());
            }

            if (isset($arg[2]))
            {
                $this->assertRegExp($arg[2], $def->getArgument($arg[0])->getDescription());
            }
        }

        foreach ($opts as $opt)
        {
            $this->assertTrue($def->hasOption($opt[0]));

            if (isset($opt[1]))
            {
                $this->assertSame($opt[1], $def->getOption($opt[0])->getShortcut());
            }

            if ($opt[2] === InputOption::VALUE_REQUIRED)
            {
                $this->assertTrue($def->getOption($opt[0])->isValueRequired());
            }
            if ($opt[2] === InputOption::VALUE_OPTIONAL)
            {
                $this->assertTrue($def->getOption($opt[0])->isValueOptional());
            }
            if ($opt[2] === InputOption::VALUE_IS_ARRAY)
            {
                $this->assertTrue($def->getOption($opt[0])->isArray());
            }

            if (isset($opt[3]))
            {
                $this->assertRegExp($opt[3], $def->getOption($opt[0])->getDescription());
            }

            if (isset($opt[4]))
            {
                $this->assertSame($opt[4], $def->getOption($opt[0])->getDefault());
            }
        }
    }

    /**
     * @param string[]|null $methods
     * @return InputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createInputMock($methods = [])
    {
        $input = $this->getMock(InputInterface::class, $methods, [], '', false);

        if (!in_array('getArgument', $methods))
        {
            $input
                ->expects($this->any())
                ->method('getArgument')
                ->will($this->returnArgument(0));
        }

        if (!in_array('getOption', $methods))
        {
            $input
                ->expects($this->any())
                ->method('getOption')
                ->will($this->returnArgument(0));
        }

        return $input;
    }

    /**
     * @param string[]|null $methods
     * @return OutputInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function createOutputMock($methods = [])
    {
        $output = $this->getMock(OutputInterface::class, $methods, [], '', false);

        return $output;
    }

    /**
     * @param string[]|null $methods
     * @return Command|\PHPUnit_Framework_MockObject_MockObject
     * @throws Exception
     */
    public function createCommand($methods = null)
    {
        if ($this->class === '')
        {
            throw new Exception('Class not set');
        }

        $channel  = $this->getMock(ChannelInterface::class, [], [], '', false);
        $receiver = 'default';

        $this->cmd = $this->getMock($this->class, $methods, [ $channel, $receiver ]);

        return $this->cmd;
    }
}
