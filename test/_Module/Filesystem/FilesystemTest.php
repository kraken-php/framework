<?php

namespace Kraken\_Module\Filesystem;

use Kraken\_Module\Filesystem\_Abstract\FilesystemTestAbstract;
use Kraken\_Module\Filesystem\_Partial\FilesystemPartial;
use Kraken\Filesystem\Filesystem;
use Kraken\Filesystem\FilesystemAdapterFactory;
use Kraken\Filesystem\FilesystemInterface;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class FilesystemTest extends FilesystemTestAbstract
{
    use FilesystemPartial;

    /**
     * @return string
     */
    public function getPrefix()
    {
        return '';
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
            return $path;
        }

        return $this->getPath($path, $replace, $with);
    }

    /**
     * @return FilesystemInterface
     */
    public function createFilesystem($path = null)
    {
        $factory = new FilesystemAdapterFactory();

        return new Filesystem(
            $factory->create('Local', [ [ 'path' => $path !== null ? $path : $this->path ] ])
        );
    }
}
