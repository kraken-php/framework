<?php

namespace Kraken\_Module\Filesystem;

use Kraken\_Module\Filesystem\_Abstract\FilesystemTestAbstract;
use Kraken\_Module\Filesystem\_Partial\FilesystemPartial;
use Kraken\Filesystem\Filesystem;
use Kraken\Filesystem\FilesystemAdapterFactory;
use Kraken\Filesystem\FilesystemInterface;
use Kraken\Filesystem\FilesystemManager;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class FilesystemManagerTest extends FilesystemTestAbstract
{
    use FilesystemPartial;

    /**
     * @return string
     */
    public function getPrefix()
    {
        return 'test://';
    }

    /**
     * @param string $path
     * @param null $replace
     * @param string $with
     * @return string
     */
    public function getPrefixed($path, $replace = null, $with = '')
    {
        if ($path === '' || $path === '/')
        {
            return 'test://';
        }

        return 'test://' . ltrim($this->getPath($path, $replace, $with), '/');
    }

    /**
     * @return FilesystemInterface
     */
    public function createFilesystem($path = null)
    {
        $factory = new FilesystemAdapterFactory();

        $fs = new Filesystem(
            $factory->create('Local', [ [ 'path' => $path !== null ? $path : $this->path ] ])
        );

        return new FilesystemManager([
            'test' => $fs
        ]);
    }
}
