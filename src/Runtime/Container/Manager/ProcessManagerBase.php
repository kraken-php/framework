<?php

namespace Kraken\Runtime\Container\Manager;

use Kraken\Channel\Extra\Request;
use Kraken\Channel\ChannelInterface;
use Kraken\Filesystem\FilesystemInterface;
use Kraken\Promise\Promise;
use Kraken\Runtime\Container\ProcessManagerInterface;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Throwable\Exception\Logic\InvalidArgumentException;
use Kraken\Throwable\Exception\Logic\ResourceOccupiedException;
use Kraken\Throwable\Exception\Runtime\ReadException;
use Kraken\Throwable\Exception\Runtime\RejectionException;
use Kraken\Util\System\SystemInterface;
use Error;
use Exception;

class ProcessManagerBase implements ProcessManagerInterface
{
    /**
     * @var RuntimeContainerInterface
     */
    protected $runtime;

    /**
     * @var ChannelInterface
     */
    protected $channel;

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
     * @param RuntimeContainerInterface $runtime
     * @param ChannelInterface $channel
     * @param SystemInterface $system
     * @param FilesystemInterface $fs
     * @throws InstantiationException
     */
    public function __construct(RuntimeContainerInterface $runtime, ChannelInterface $channel, SystemInterface $system, FilesystemInterface $fs)
    {
        $this->runtime = $runtime;
        $this->channel = $channel;
        $this->system  = $system;
        $this->fs = $fs;

        $this->scriptRoot = $runtime->getCore()->getDataPath() . '/autorun';
        $this->fsPath = $runtime->getCore()->getDataDir() . '/storage/process/' . $runtime->getAlias() . '/manager/processes.json';
        $this->processes = [];

        try
        {
            $this->processes = $this->selectFromStorage();
        }
        catch (Error $ex)
        {
            throw new InstantiationException('ProcessManagerBase could not be initialized.', $ex);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException('ProcessManagerBase could not be initialized.', $ex);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->runtime);
        unset($this->channel);
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
     * @override
     * @inheritDoc
     */
    public function createProcess($alias, $name, $flags = Runtime::CREATE_DEFAULT)
    {
        if (isset($this->processes[$alias]))
        {
            if ($name === null || $name === 'null')
            {
                $name = $this->processes[$alias]['name'];
            }

            $manager = $this;

            if ($flags === Runtime::CREATE_DEFAULT && $this->processes[$alias]['verified'] === false)
            {
                $req = $this->createRequest(
                    $this->channel, $alias, new RuntimeCommand('cmd:ping')
                );

                return $req->call()
                    ->then(
                        function() {
                            return 'Process has been created.';
                        },
                        function() use($manager, $alias, $name) {
                            return $manager->createProcess($alias, $name, Runtime::CREATE_FORCE_HARD);
                        }
                    );
            }
            else if ($flags === Runtime::CREATE_FORCE_SOFT)
            {
                return $this
                    ->destroyProcess($alias, Runtime::DESTROY_FORCE_SOFT)
                    ->then(
                        function() use($manager, $alias, $name) {
                            return $manager->createProcess($alias, $name);
                        }
                    );
            }
            else if ($flags === Runtime::CREATE_FORCE_HARD)
            {
                return $this
                    ->destroyProcess($alias, Runtime::DESTROY_FORCE_HARD)
                    ->then(
                        function() use($manager, $alias, $name) {
                            return $manager->createProcess($alias, $name);
                        }
                    );
            }
            else if ($flags === Runtime::CREATE_FORCE)
            {
                return $this->destroyProcess($alias, Runtime::DESTROY_FORCE)
                    ->then(
                        function() use($manager, $alias, $name) {
                            return $manager->createProcess($alias, $name);
                        }
                    );
            }
            else
            {
                return Promise::doReject(new ResourceOccupiedException('Process with such alias already exists.'));
            }
        }
        else if ($name === null)
        {
            return Promise::doReject(
                new InvalidArgumentException('Name of new process cannot be null.')
            );
        }

        $pid = $this->system->run($this->phpCommand('kraken.process', [ $this->runtime->getAlias(), $alias, $name ]));

        if (!$this->system->existsPid($pid))
        {
            return Promise::doReject(new ResourceOccupiedException('Process could not be created.'));
        }

        if (!$this->allocateProcess($alias, $name, $pid))
        {
            return Promise::doReject(new ResourceOccupiedException('Process could not be created because of storage failure.'));
        }

        $req = $this->createRequest(
            $this->channel, $alias, new RuntimeCommand('cmd:ping')
        );

        return $req
            ->call()
            ->then(
                function() {
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
     * @override
     * @inheritDoc
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
                new ResourceOccupiedException("Process [$alias] could not be destroyed with force level=DESTROY_KEEP.")
            );
        }
        else if ($flags === Runtime::DESTROY_FORCE_SOFT)
        {
            $req = $this->createRequest(
                $this->channel,
                $alias,
                new RuntimeCommand('container:destroy')
            );

            return $req
                ->call()
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
            return $manager
                ->destroyProcess($alias, Runtime::DESTROY_FORCE_SOFT)
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
                new ResourceOccupiedException("Process with pid [$pid] could not be killed forcefully.")
            );
        }

        return Promise::doResolve()
            ->then(
                function() use($manager, $alias) {
                    $manager->freeProcess($alias);
                    return "Process has been destroyed!";
                }
            );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startProcess($alias)
    {
        $req = $this->createRequest(
            $this->channel,
            $alias,
            new RuntimeCommand('container:start')
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopProcess($alias)
    {
        $req = $this->createRequest(
            $this->channel,
            $alias,
            new RuntimeCommand('container:stop')
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function flushProcesses($flags = Runtime::DESTROY_KEEP)
    {
        $promises = [];

        if ($flags === Runtime::DESTROY_KEEP)
        {
            return Promise::doReject(
                new RejectionException('Process storage could not be flushed because of force level set to DESTROY_KEEP.')
            );
        }

        foreach ($this->processes as $alias=>$process)
        {
            $promises[] = $this->destroyProcess($alias, $flags);
        }

        return Promise::all($promises)
            ->then(
                function() {
                    $this->processes = [];
                    $this->updateStorage();
                    return 'Processes storage has been flushed.';
                }
            );
    }

    /**
     * Allocate process.
     *
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
     * Free process.
     *
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
     * Create Request.
     *
     * @param ChannelInterface $channel
     * @param string $receiver
     * @param string $command
     * @return Request
     */
    protected function createRequest(ChannelInterface $channel, $receiver, $command)
    {
        return new Request($channel, $receiver, $command);
    }

    /**
     * Run external PHP script.
     *
     * @param string $command
     * @param string[] $params
     * @return string
     */
    private function phpCommand($command, $params = [])
    {
        return implode(' ', array_merge([ PHP_BINARY, realpath($this->scriptRoot . DIRECTORY_SEPARATOR . $command) ], $params));
    }

    /**
     * Copy temporary process allocation data to persistent storage.
     *
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
        $this->fs->create($this->fsPath, json_encode($data));
    }

    /**
     * Copy data from persistent storage to temporary one.
     *
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
