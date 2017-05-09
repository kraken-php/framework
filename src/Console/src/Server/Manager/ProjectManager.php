<?php

namespace Kraken\Console\Server\Manager;

use Kraken\Channel\Extra\Request;
use Kraken\Channel\ChannelInterface;
use Kraken\Filesystem\FilesystemInterface;
use Kraken\Promise\Promise;
use Kraken\Runtime\RuntimeCommand;
use Kraken\Runtime\Runtime;
use Kraken\Runtime\RuntimeContainerInterface;
use Kraken\Throwable\Exception\Logic\InstantiationException;
use Kraken\Throwable\Exception\Logic\ResourceOccupiedException;
use Kraken\Throwable\Exception\Runtime\ReadException;
use Kraken\Util\System\SystemInterface;
use Error;
use Exception;

class ProjectManager implements ProjectManagerInterface
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
     * @var SystemInterface
     */
    protected $system;

    /**
     * @var FilesystemInterface
     */
    protected $fs;

    /**
     * @var string
     */
    protected $scriptRoot;

    /**
     * @var string
     */
    protected $fsPath;

    /**
     * @var string
     */
    protected $projectRoot;

    /**
     * @var string
     */
    protected $projectName;

    /**
     * @var mixed
     */
    protected $data;

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

        $core = $runtime->getCore();
        $this->scriptRoot = $core->getDataPath() . '/autorun';
        $this->fsPath = $core->getDataDir() . '/storage/project/project.json';
        $this->projectRoot = 'Main';
        $this->projectName = 'Main';
        $this->data = $this->getEmptyStorage();

        try
        {
            $this->data = $this->selectFromStorage();
        }
        catch (Error $ex)
        {
            throw new InstantiationException('ProjectManager could not be initialized.', $ex);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException('ProjectManager could not be initialized.', $ex);
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
        unset($this->projectRoot);
        unset($this->projectName);
        unset($this->data);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setProjectRoot($root)
    {
        $this->projectRoot = $root;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getProjectRoot()
    {
        return $this->projectRoot;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setProjectName($name)
    {
        $this->projectName = $name;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsProject()
    {
        return isset($this->data['pid']);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createProject($flags = Runtime::CREATE_DEFAULT)
    {
        $manager = $this;
        $alias = $this->projectRoot;
        $name  = $this->projectName;

        if (isset($this->data['pid']))
        {
            if ($flags === Runtime::CREATE_FORCE_SOFT)
            {
                return $this
                    ->destroyProject(Runtime::DESTROY_FORCE_SOFT)
                    ->then(
                        function() use($manager) {
                            return $manager->createProject();
                        }
                    );
            }
            else if ($flags === Runtime::CREATE_FORCE_HARD)
            {
                return $this
                    ->destroyProject(Runtime::DESTROY_FORCE_HARD)
                    ->then(
                        function() use($manager) {
                            return $manager->createProject();
                        }
                    );
            }
            else if ($flags === Runtime::CREATE_FORCE)
            {
                return $this
                    ->destroyProject(Runtime::DESTROY_FORCE)
                    ->then(
                        function() use($manager) {
                            return $manager->createProject();
                        }
                    );
            }
            else
            {
                return Promise::doReject(new ResourceOccupiedException('Project already exists.'));
            }
        }

        $pid = $this->system->run($this->phpCommand('kraken.process', [ 'undefined', $alias, $name ]));

        if (!$this->system->existsPid($pid))
        {
            return Promise::doReject(new ResourceOccupiedException('Project could not be created.'));
        }

        if (!$this->allocateProject($alias, $name, $pid))
        {
            return Promise::doReject(new ResourceOccupiedException('Project could not be created because of storage failure.'));
        }

        $req = $this->createRequest(
            $this->channel, $alias, new RuntimeCommand('cmd:ping')
        );

        return $req
            ->call()
            ->then(
                function() {
                    return 'Project has been created.';
                },
                function($reason) {
                    $this->freeProject();
                    throw $reason;
                },
                function($reason) {
                    $this->freeProject();
                    return $reason;
                }
            );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function destroyProject($flags = Runtime::DESTROY_FORCE_SOFT)
    {
        if (!isset($this->data['pid']))
        {
            return Promise::doResolve("Project was not needed to be destroyed, because it had not existed.");
        }

        $manager = $this;
        $pid   = $this->data['pid'];
        $alias = $this->data['alias'];
        $name  = $this->data['name'];

        if ($flags === Runtime::DESTROY_KEEP)
        {
            return Promise::doReject(
                new ResourceOccupiedException("Project could not be destroyed with force level=DESTROY_KEEP.")
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
                    function($value) use($manager) {
                        usleep(1e3);
                        $manager->freeProject();
                        return $value;
                    }
                );
        }
        else if ($flags === Runtime::DESTROY_FORCE)
        {
            return $this
                ->destroyProject(Runtime::DESTROY_FORCE_SOFT)
                ->then(
                    null,
                    function() use($manager) {
                        return $manager->destroyProject(Runtime::DESTROY_FORCE_HARD);
                    }
                );
        }

        if (!$this->system->existsPid($pid))
        {
            return Promise::doResolve()
                ->then(
                    function() use($manager) {
                        $manager->freeProject();
                        return "Project was not needed to be destroyed, because it had not existed.";
                    }
                );
        }
        else if (!$this->system->kill($pid))
        {
            return Promise::doReject(
                new ResourceOccupiedException("Project could not be killed forcefully.")
            );
        }

        return Promise::doResolve()
            ->then(
                function() use($manager) {
                    $manager->freeProject();
                    return "Process has been destroyed!";
                }
            );
    }

    /**
     * @override
     * @inheritDoc
     */
    public function startProject()
    {
        $req = $this->createRequest(
            $this->channel,
            $this->projectRoot,
            new RuntimeCommand('container:start')
        );

        return $req->call();
    }

    /**
     * @override
     * @inheritDoc
     */
    public function stopProject()
    {
        $req = $this->createRequest(
            $this->channel,
            $this->projectRoot,
            new RuntimeCommand('container:stop')
        );

        return $req->call();
    }

    /**
     * Allocate project data.
     *
     * @internal
     * @param string $alias
     * @param string $name
     * @param string $pid
     * @return bool
     */
    public function allocateProject($alias, $name, $pid)
    {
        try
        {
            $record = [
                'pid'   => $pid,
                'alias' => $alias,
                'name'  => $name
            ];

            $this->updateStorage($record);
            $this->data = array_merge($this->data, $record);
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
     * Flush project data.
     *
     * @internal
     * @return bool
     */
    public function freeProject()
    {
        try
        {
            $this->data = $this->getEmptyStorage();
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
     * Copy temporary project data to persistent storage.
     *
     * @param string[] $with
     * @throws ReadException
     */
    private function updateStorage($with = [])
    {
        $data = array_merge($this->data, $with);
        $this->fs->create($this->fsPath, json_encode($data));
    }

    /**
     * Copy data from persistent storage to temporary one.
     *
     * @return mixed
     * @throws ReadException
     */
    private function selectFromStorage()
    {
        if (!$this->fs->exists($this->fsPath))
        {
            return $this->getEmptyStorage();
        }

        $data = json_decode($this->fs->read($this->fsPath), true);

        if (!isset($data['pid']) || !$this->system->existsPid($data['pid']))
        {
            return $this->getEmptyStorage();
        }

        return $data;
    }

    /**
     *
     */
    private function getEmptyStorage()
    {
        return [];
    }
}
