<?php

namespace Kraken\Runtime\Process\Manager;

use Kraken\Core\EnvironmentInterface;
use Kraken\Exception\Io\ReadException;
use Kraken\Exception\Runtime\InstantiationException;
use Kraken\Exception\Runtime\InvalidArgumentException;
use Kraken\Exception\Runtime\RejectionException;
use Kraken\Filesystem\FilesystemInterface;
use Kraken\Promise\Promise;
use Kraken\Promise\PromiseInterface;
use Kraken\Channel\ChannelBaseInterface;
use Kraken\Channel\Extra\Request;
use Kraken\Exception\Resource\ResourceDefinedException;
use Kraken\Runtime\Process\ProcessManagerInterface;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeInterface;
use Kraken\System\SystemInterface;
use Error;
use Exception;

class ProcessManagerBase implements ProcessManagerInterface
{
    /**
     * @var RuntimeInterface
     */
    protected $runtime;

    /**
     * @var ChannelBaseInterface
     */
    protected $channel;

    /**
     * @var EnvironmentInterface
     */
    protected $env;

    /**
     * @var FilesystemInterface
     */
    protected $fs;

    /**
     * @var SystemInterface
     */
    protected $system;

    /**
     * @var string
     */
    protected $scriptRoot;

    /**
     * @var string
     */
    protected $fsPath;

    /**
     * @var string[][]
     */
    protected $processes;

    /**
     * @param RuntimeInterface $runtime
     * @param ChannelBaseInterface $channel
     * @param EnvironmentInterface $env
     * @param SystemInterface $system
     * @param FilesystemInterface $fs
     * @throws InstantiationException
     */
    public function __construct(RuntimeInterface $runtime, ChannelBaseInterface $channel, EnvironmentInterface $env, SystemInterface $system, FilesystemInterface $fs)
    {
        $this->runtime = $runtime;
        $this->channel = $channel;
        $this->env = $env;
        $this->system = $system;
        $this->fs = $fs;

        $this->scriptRoot = $runtime->core()->dataPath() . '/autorun';
        $this->fsPath = $runtime->core()->dataDir() . '/tmp/process/' . $runtime->alias() . '/manager/processes.json';
        $this->processes = [];

        try
        {
            $this->processes = $this->selectFromStorage();
        }
        catch (Error $ex)
        {
            throw new InstantiationException('ProcessManagerBase could not be initialized.');
        }
        catch (Exception $ex)
        {
            throw new InstantiationException('ProcessManagerBase could not be initialized.');
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->runtime);
        unset($this->channel);
        unset($this->env);
        unset($this->system);
        unset($this->fs);

        unset($this->scriptRoot);
        unset($this->fsPath);
        unset($this->processes);
    }

    /**
     * @param string $alias
     * @return bool
     */
    public function existsProcess($alias)
    {
        return isset($this->processes[$alias]);
    }

    /**
     * @param string $alias
     * @param string|null $name
     * @param int $flags
     * @return PromiseInterface
     */
    public function createProcess($alias, $name, $flags = Runtime::CREATE_DEFAULT)
    {
        if (isset($this->processes[$alias]))
        {
            if ($name === null || $name === 'null')
            {
                $name = $this->processes[$alias]['name'];
            }

            if ($flags === Runtime::CREATE_DEFAULT && $this->processes[$alias]['verified'] === false)
            {
                $manager = $this;
                $req = new Request(
                    $this->channel, $alias, new RuntimeCommand('cmd:ping')
                );

                return $req->call()
                    ->then(
                        function($response) {
                            return 'Process has been created.';
                        },
                        function() use($manager, $alias, $name) {
                            return $manager->createProcess($alias, $name, Runtime::CREATE_FORCE_HARD);
                        }
                    );
            }
            else if ($flags === Runtime::CREATE_FORCE_SOFT)
            {
                $manager = $this;
                return $this->destroyProcess($alias, Runtime::DESTROY_FORCE_SOFT)
                    ->then(
                        function() use($manager, $alias, $name) {
                            return $manager->createProcess($alias, $name);
                        }
                    );
            }
            else if ($flags === Runtime::CREATE_FORCE_HARD)
            {
                $manager = $this;
                return $this->destroyProcess($alias, Runtime::DESTROY_FORCE_HARD)
                    ->then(
                        function() use($manager, $alias, $name) {
                            return $manager->createProcess($alias, $name);
                        }
                    );
            }
            else if ($flags === Runtime::CREATE_FORCE)
            {
                $manager = $this;
                return $this->destroyProcess($alias, Runtime::DESTROY_FORCE)
                    ->then(
                        function() use($manager, $alias, $name) {
                            return $manager->createProcess($alias, $name);
                        }
                    );
            }
            else
            {
                return Promise::doReject(new ResourceDefinedException('Process with such alias already exists.'));
            }
        }
        else if ($name === null)
        {
            return Promise::doReject(
                new InvalidArgumentException('Name of new process cannot be null.')
            );
        }

        $pid = $this->system->run($this->phpCommand('kraken.process', [ $this->runtime->alias(), $alias, $name ]));

        if (!$this->system->existsPid($pid))
        {
            return Promise::doReject(new ResourceDefinedException('Process could not be created.'));
        }

        if (!$this->allocateProcess($alias, $name, $pid))
        {
            return Promise::doReject(new ResourceDefinedException('Process could not be created because of storage failure.'));
        }

        $req = new Request(
            $this->channel, $alias, new RuntimeCommand('cmd:ping')
        );

        return $req->call()
            ->then(
                function($response) {
                    return 'Process has been created.';
                },
                function($reason) use($alias) {
                    $this->freeProcess($alias);
                    throw $reason;
                },
                function($reason) use($alias) {
                    $this->freeProcess($alias);
                    return $reason;
                }
            );
    }

    /**
     * @param string $alias
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyProcess($alias, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        if (!isset($this->processes[$alias]))
        {
            return Promise::doResolve("Process [$alias] was not needed to be destroyed, because it had not existed.");
        }

        $pid = $this->processes[$alias]['pid'];
        $manager = $this;

        if ($flags === Runtime::DESTROY_KEEP)
        {
            return Promise::doReject(
                new ResourceDefinedException("Process [$alias] could not be destroyed with force level=DESTROY_KEEP.")
            );
        }
        else if ($flags === Runtime::DESTROY_FORCE_SOFT)
        {
            $req = new Request(
                $this->channel,
                $alias,
                new RuntimeCommand('container:destroy')
            );

            return $req->call()
                ->then(
                    function($value) use($manager, $alias) {
                        usleep(1e3);
                        $manager->freeProcess($alias);
                        return $value;
                    }
                );
        }
        else if ($flags === Runtime::DESTROY_FORCE)
        {
            $manager = $this;
            return $manager->destroyProcess($alias, Runtime::DESTROY_FORCE_SOFT)
                ->then(
                    null,
                    function() use($manager, $alias) {
                        return $manager->destroyProcess($alias, Runtime::DESTROY_FORCE_HARD);
                    }
                );
        }

        if (!$this->system->existsPid($pid))
        {
            return Promise::doResolve()
                ->then(
                    function() use($manager, $alias) {
                        $manager->freeProcess($alias);
                    }
                )
                ->then(
                    function() use($pid) {
                        return "Process with pid [$pid] was not needed to be destroyed, because it had not existed.";
                    }
                );
        }
        else if (!$this->system->kill($pid))
        {
            return Promise::doReject(
                new ResourceDefinedException("Process with pid [$pid] could not be killed forcefully.")
            );
        }

        return Promise::doResolve()
            ->then(
                function() use($manager, $alias) {
                    $manager->freeProcess($alias);
                }
            )
            ->then(
                function() {
                    return "Process has been destroyed!";
                }
            );
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function startProcess($alias)
    {
        $req = new Request(
            $this->channel,
            $alias,
            new RuntimeCommand('container:start')
        );

        return $req->call();
    }

    /**
     * @param string $alias
     * @return PromiseInterface
     */
    public function stopProcess($alias)
    {
        $req = new Request(
            $this->channel,
            $alias,
            new RuntimeCommand('container:stop')
        );

        return $req->call();
    }

    /**
     * @param string[][] $definitions
     * @param int $flags
     * @return PromiseInterface
     */
    public function createProcesses($definitions, $flags = Runtime::CREATE_DEFAULT)
    {
        $promises = [];

        foreach ($definitions as $alias=>$name)
        {
            $promises[] = $this->createProcess($alias, $name, $flags);
        }

        return Promise::all($promises)
            ->then(
                function() {
                    return 'Processes have been created.';
                },
                function() {
                    throw new RejectionException('Some of the processes could not be created.');
                }
            );
    }

    /**
     * @param string[] $aliases
     * @param int $flags
     * @return PromiseInterface
     */
    public function destroyProcesses($aliases, $flags = Runtime::DESTROY_FORCE_SOFT)
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->destroyProcess($alias, $flags);
        }

        return Promise::all($promises)
            ->then(
                function() {
                    return 'Processes have been destroyed.';
                },
                function() {
                    throw new RejectionException('Some of the processes could not be destroyed.');
                }
            );
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function startProcesses($aliases)
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->startProcess($alias);
        }

        return Promise::all($promises)
            ->then(
                function() {
                    return 'Processes have been started.';
                },
                function() {
                    throw new RejectionException('Some of the processes could not be started.');
                }
            );
    }

    /**
     * @param $aliases
     * @return PromiseInterface
     */
    public function stopProcesses($aliases)
    {
        $promises = [];

        foreach ($aliases as $alias)
        {
            $promises[] = $this->stopProcess($alias);
        }

        return Promise::all($promises)
            ->then(
                function() {
                    return 'Processes have been stopped.';
                },
                function() {
                    throw new RejectionException('Some of the processes could not be stopped.');
                }
            );
    }

    /**
     * @return PromiseInterface
     */
    public function getProcesses()
    {
        $list = [];
        foreach ($this->processes as $alias=>$process)
        {
            $list[] = $alias;
        }

        return Promise::doResolve($list);
    }

    /**
     * @param int $flags
     * @return PromiseInterface
     */
    public function flushProcesses($flags = Runtime::DESTROY_KEEP)
    {
        $promises = [];

        if ($flags !== Runtime::DESTROY_KEEP)
        {
            foreach ($this->processes as $alias=>$process)
            {
                $promises[] = $this->destroyProcess($alias, $flags);
            }
        }

        return Promise::all($promises)
            ->always(function() {
                $this->processes = [];
                $this->updateStorage();
            })
            ->then(function() {
                return 'Processes storage has been flushed.';
            })
        ;
    }

    /**
     * @internal
     * @param string $alias
     * @param string $name
     * @param string $pid
     * @return bool
     */
    public function allocateProcess($alias, $name, $pid)
    {
        try
        {
            $record = [
                'pid'      => $pid,
                'name'     => $name,
                'verified' => true
            ];

            $this->updateStorage([ $alias => $record ]);
            $this->processes[$alias] = $record;
        }
        catch (Error $ex)
        {
            return false;
        }
        catch (Exception $ex)
        {
            return false;
        }

        return true;
    }

    /**
     * @internal
     * @param string $alias
     * @return bool
     */
    public function freeProcess($alias)
    {
        try
        {
            unset($this->processes[$alias]);
            $this->updateStorage();
        }
        catch (Error $ex)
        {
            return false;
        }
        catch (Exception $ex)
        {
            return false;
        }

        return true;
    }

    /**
     * @param string $command
     * @param string[] $params
     * @return string
     */
    private function phpCommand($command, $params = [])
    {
        return implode(' ', array_merge([ PHP_BINARY, realpath($this->scriptRoot . DIRECTORY_SEPARATOR . $command) ], $params));
    }

    /**
     * @param string[] $with
     * @throws ReadException
     */
    private function updateStorage($with = [])
    {
        $data = [];

        foreach ($this->processes as $processAlias=>$process)
        {
            $data[$processAlias] = $process;
        }

        $data = array_merge($data, $with);
        $this->fs->write($this->fsPath, json_encode($data));
    }

    /**
     * @return string[][]
     * @throws ReadException
     */
    private function selectFromStorage()
    {
        if (!$this->fs->exists($this->fsPath))
        {
            return [];
        }

        $processes = [];
        $data = json_decode($this->fs->read($this->fsPath), true);

        foreach ($data as $alias=>$record)
        {
            if ($this->system->existsPid($record['pid']))
            {
                $processes[$alias] = [
                    'pid'       => $record['pid'],
                    'name'      => $record['name'],
                    'verified'  => false
                ];
            }
        }

        return $processes;
    }
}
