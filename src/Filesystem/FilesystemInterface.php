<?php

namespace Kraken\Filesystem;

use Kraken\Throwable\Exception\Runtime\Io\IoReadException;
use Kraken\Throwable\Exception\Runtime\Io\IoWriteException;

interface FilesystemInterface
{
    /**
     * Check whether a file or directory exist.
     *
     * @param string $path
     * @return bool
     * @throws IoReadException
     */
    public function exists($path);

    /**
     * Move (rename) a file or directory.
     *
     * @param string $source
     * @param string $destination
     * @throws IoWriteException
     */
    public function move($source, $destination);

    /**
     * Check if path is a file.
     *
     * @param string $path
     * @return bool
     * @throws IoReadException
     */
    public function isFile($path);

    /**
     * Check if path is a file.
     *
     * @param string $path
     * @return bool
     * @throws IoReadException
     */
    public function isDir($path);

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     * @param string $filterPattern
     * @return array
     * @throws IoReadException
     */
    public function getContents($directory = '', $recursive = false, $filterPattern = '');

    /**
     * List files of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     * @param string $filterPattern
     * @return array
     * @throws IoReadException
     */
    public function getFiles($directory = '', $recursive = false, $filterPattern = '');

    /**
     * List directories of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     * @param string $filterPattern
     * @return array
     * @throws IoReadException
     */
    public function getDirectories($directory = '', $recursive = false, $filterPattern = '');

    /**
     * Get visibility of a file or directory.
     *
     * @param string $path
     * @return string
     * @throws IoReadException
     */
    public function getVisibility($path);

    /**
     * Check if file or directory is public.
     *
     * @param string $path
     * @return bool
     * @throws IoReadException
     */
    public function isPublic($path = '');

    /**
     * Check if file or directory is private.
     *
     * @param string $path
     * @return bool
     * @throws IoReadException
     */
    public function isPrivate($path = '');

    /**
     * Set visibility of a file or directory.
     *
     * @param string $path
     * @param string $visibility
     * @throws IoWriteException
     */
    public function setVisibility($path, $visibility);

    /**
     * Sets visibility of file or directory to public.
     *
     * @param string $path
     * @throws IoWriteException
     */
    public function setPublic($path = '');

    /**
     * Sets visibility of file or directory to private.
     *
     * @param string $path
     * @throws IoWriteException
     */
    public function setPrivate($path = '');

    /**
     * Create a new file.
     *
     * @param string $path
     * @param string $contents
     * @param string $visibility
     * @throws IoWriteException
     */
    public function create($path, $contents = '', $visibility = Filesystem::VISIBILITY_DEFAULT);

    /**
     * Create a file or update if exists.
     *
     * @param string $path
     * @param string $contents
     * @param string $visibility
     * @throws IoWriteException
     */
    public function write($path, $contents = '', $visibility = Filesystem::VISIBILITY_DEFAULT);

    /**
     * Appends contents to file.
     *
     * @param string $path
     * @param string $contents
     * @throws IoWriteException
     */
    public function append($path, $contents);

    /**
     * Prepends contents to file.
     *
     * @param string $path
     * @param string $contents
     * @throws IoWriteException
     */
    public function prepend($path, $contents);

    /**
     * Read a file.
     *
     * @param string $path
     * @return string
     * @throws IoReadException
     */
    public function read($path);

    /**
     * Require a file.
     *
     * @param string $path
     * @return mixed
     * @throws IoReadException
     */
    public function req($path);

    /**
     * Copy a file.
     *
     * @param string $source
     * @param string $destination
     * @throws IoWriteException
     */
    public function copy($source, $destination);

    /**
     * Remove a file.
     *
     * @param string $path
     * @throws IoWriteException
     */
    public function remove($path);

    /**
     * Erase a file.
     *
     * @param string $path
     * @throws IoWriteException
     */
    public function erase($path);

    /**
     * Returns size of a file.
     *
     * @param $path
     * @return int
     * @throws IoReadException
     */
    public function getSize($path);

    /**
     * Get type of a file.
     *
     * @param string $path
     * @return string
     * @throws IoReadException
     */
    public function getType($path);

    /**
     * Get mimetype of a file.
     *
     * @param string $path
     * @return string
     * @throws IoReadException
     */
    public function getMimetype($path);

    /**
     * Get timestamp of a file.
     *
     * @param string $path
     * @return string
     * @throws IoReadException
     */
    public function getTimestamp($path);

    /**
     * Get extension of a file.
     *
     * @param string $path
     * @return string
     * @throws IoReadException
     */
    public function getExtension($path);

    /**
     * Create a directory.
     *
     * @param string $dirname
     * @param string $visibility
     * @throws IoWriteException
     */
    public function createDir($dirname, $visibility = Filesystem::VISIBILITY_DEFAULT);

    /**
     * Copy a directory.
     *
     * @param string $source
     * @param string $destination
     * @throws IoWriteException
     */
    public function copyDir($source, $destination);

    /**
     * Remove a directory.
     *
     * @param string $dirname
     * @throws IoWriteException
     */
    public function removeDir($dirname);

    /**
     * Erase a directory.
     *
     * @param string $dirname
     * @throws IoWriteException
     */
    public function eraseDir($dirname = '');
}
