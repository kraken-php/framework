<?php

namespace Kraken\Console\Server\Manager;

use Kraken\Promise\PromiseInterface;
use Kraken\Runtime\Runtime;

interface ProjectManagerInterface
{
    /**
     * Set project Root.
     *
     * @param string $root
     */
    public function setProjectRoot($root);

    /**
     * Get project Root.
     *
     * @return string $root
     */
    public function getProjectRoot();

    /**
     * Set project Name.
     *
     * @param string $name
     */
    public function setProjectName($name);

    /**
     * Get project Name
     *
     * @return string $root
     */
    public function getProjectName();

    /**
     * Check if project exists.
     *
     * @return bool
     */
    public function existsProject();

    /**
     * Create a new project.
     *
     * Flags might be one of:
     * Runtime::CREATE_DEFAULT - creates a new project only if it does not already exist.
     * Runtime::CREATE_FORCE_SOFT - does the same as CREATE_DEFAULT, but in case of existing project tries to replace
     * it. Replacement is done by destroying existing project by sending shutdown message.
     * Runtime::CREATE_FORCE_HARD - does the same as CREATE_DEFAULT, but in case of existing project tries to replace
     * it. Replacement is done by forcefully destroying existing project.
     * Runtime::CREATE_FORCE - creates a new project if it does not exist or tries to replace existing firstly trying
     * to destroy it gracefully, but it fails doing it forcefully.
     *
     * @param int $flags
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function createProject($flags = Runtime::CREATE_DEFAULT);

    /**
     * Destroy project.
     *
     * Flags might be one of:
     * Runtime::DESTROY_KEEP - sets manager to not destroy project
     * Runtime::DESTROY_FORCE_SOFT - destroys project by sending message to shutdown
     * Runtime::DESTROY_FORCE_HARD - destroys project forcefully and immediately
     * Runtime::DESTROY_FORCE - first, tries to gracefully destroy project by sending message to shutdown, if it does
     * not receive answer, then closes it forcefully.
     *
     * @param int $flags
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function destroyProject($flags = Runtime::DESTROY_FORCE_SOFT);

    /**
     * Start existing project.
     *
     * @param string $alias
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function startProject();

    /**
     * Stop existing project.
     *
     * @return PromiseInterface
     * @resolves mixed
     * @rejects Error|Exception|string|null
     */
    public function stopProject();
}
