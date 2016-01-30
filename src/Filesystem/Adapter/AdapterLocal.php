<?php

namespace Kraken\Filesystem\Adapter;

use Exception;
use League\Flysystem\Adapter\Local;
use Kraken\Exception\Runtime\InstantiationException;
use Kraken\Filesystem\FilesystemAdapterInterface;

class AdapterLocal extends Local implements FilesystemAdapterInterface
{
    /**
     * @param string $rootDir
     * @param int $writeFlags
     * @param int $linkHandling
     * @param array $permissions
     * @throws InstantiationException
     */
    public function __construct($rootDir = null, $writeFlags = LOCK_EX, $linkHandling = parent::DISALLOW_LINKS, $permissions = [])
    {
        static::$permissions = [
            'file' => [
                'public'  => 0744,
                'private' => 0700,
            ],
            'dir' => [
                'public'  => 0755,
                'private' => 0700,
            ]
        ];

        try
        {
            parent::__construct($rootDir, $writeFlags, $linkHandling, $permissions);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException("AdapterLocal could not be initalized.", $ex);
        }
    }
}