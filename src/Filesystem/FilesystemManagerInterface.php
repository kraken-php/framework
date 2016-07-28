<?php

namespace Kraken\Filesystem;

interface FilesystemManagerInterface extends FilesystemInterface
{
    /**
     * Mount collection of FilesystemInterfaces.
     *
     * @param FilesystemInterface[] $filesystems
     */
    public function mountFilesystems($filesystems);

    /**
     * Check if there is a FilesystemInterface saved under $prefix key.
     *
     * @param string $prefix
     * @return bool
     */
    public function existsFilesystem($prefix);

    /**
     * Mount FilesystemInterface $filesystem under $prefix key.
     *
     * @param string $prefix
     * @param FilesystemInterface $filesystem
     */
    public function mountFilesystem($prefix, FilesystemInterface $filesystem);

    /**
     * Unmount FilesystemInterface saved under $prefix key.
     *
     * @param string $prefix
     */
    public function unmountFilesystem($prefix);

    /**
     * Return FilesystemInterface saved under $prefix key or null if it does not exist.
     *
     * @param string $prefix
     * @return FilesystemInterface
     */
    public function getFilesystem($prefix);
}
