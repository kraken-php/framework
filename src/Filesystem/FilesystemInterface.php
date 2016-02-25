<?php

namespace Kraken\Filesystem;

use Kraken\Throwable\Io\ReadException;
use Kraken\Throwable\Io\WriteException;

interface FilesystemInterface
{
    /**
     * Check whether a file or directory exist.
     *
     * @param string $path
     * @return bool
     * @throws ReadException
     */
    public function exists($path);

    /**
     * Move (rename) a file or directory.
     *
     * @param string $source
     * @param string $destination
     * @throws WriteException
     */
    public function move($source, $destination);

    /**
     * Check if path is a file.
     *
     * @param string $path
     * @return bool
     * @throws ReadException
     */
    public function isFile($path);

    /**
     * Check if path is a file.
     *
     * @param string $path
     * @return bool
     * @throws ReadException
     */
    public function isDir($path);

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     * @param string $filterPattern
     * @return array
     * @throws ReadException
     */
    public function contents($directory = '', $recursive = false, $filterPattern = '');

    /**
     * List files of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     * @param string $filterPattern
     * @return array
     * @throws ReadException
     */
    public function files($directory = '', $recursive = false, $filterPattern = '');

    /**
     * List directories of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     * @param string $filterPattern
     * @return array
     * @throws ReadException
     */
    public function directories($directory = '', $recursive = false, $filterPattern = '');

    /**
     * Get visibility of a file or directory.
     *
     * @param string $path
     * @return string
     * @throws ReadException
     */
    public function visibility($path);

    /**
     * Check if file or directory is public.
     *
     * @param string $path
     * @return bool
     * @throws ReadException
     */
    public function isPublic($path = '');

    /**
     * Check if file or directory is private.
     *
     * @param string $path
     * @return bool
     * @throws ReadException
     */
    public function isPrivate($path = '');

    /**
     * Sets visibility of file or directory to public.
     *
     * @param string $path
     * @throws WriteException
     */
    public function setPublic($path = '');

    /**
     * Sets visibility of file or directory to private.
     *
     * @param string $path
     * @throws WriteException
     */
    public function setPrivate($path = '');

    /**
     * Create a new file.
     *
     * @param string $path
     * @param string $contents
     * @param string $visibility
     * @throws WriteException
     */
    public function create($path, $contents = '', $visibility = Filesystem::VISIBILITY_DEFAULT);

    /**
     * Create a file or update if exists.
     *
     * @param string $path
     * @param string $contents
     * @param string $visibility
     * @throws WriteException
     */
    public function write($path, $contents = '', $visibility = Filesystem::VISIBILITY_DEFAULT);

    /**
     * Appends contents to file.
     *
     * @param string $path
     * @param string $contents
     * @throws WriteException
     */
    public function append($path, $contents);

    /**
     * Prepends contents to file.
     *
     * @param string $path
     * @param string $contents
     * @throws WriteException
     */
    public function prepend($path, $contents);

    /**
     * Read a file.
     *
     * @param string $path
     * @return string
     * @throws ReadException
     */
    public function read($path);

    /**
     * Require a file.
     *
     * @param string $path
     * @return mixed
     * @throws ReadException
     */
    public function req($path);

    /**
     * Copy a file.
     *
     * @param string $source
     * @param string $destination
     * @throws WriteException
     */
    public function copy($source, $destination);

    /**
     * Remove a file.
     *
     * @param string $path
     * @throws WriteException
     */
    public function remove($path);

    /**
     * Erase a file.
     *
     * @param string $path
     * @throws WriteException
     */
    public function erase($path);

    /**
     * Returns size of a file.
     *
     * @param $path
     * @return int
     * @throws ReadException
     */
    public function size($path);

    /**
     * Get type of a file.
     *
     * @param string $path
     * @return string
     * @throws ReadException
     */
    public function type($path);

    /**
     * Get mimetype of a file.
     *
     * @param string $path
     * @return string
     * @throws ReadException
     */
    public function mimetype($path);

    /**
     * Get timestamp of a file.
     *
     * @param string $path
     * @return string
     * @throws ReadException
     */
    public function timestamp($path);

    /**
     * Get extension of a file.
     *
     * @param string $path
     * @return string
     * @throws ReadException
     */
    public function extension($path);

    /**
     * Create a directory.
     *
     * @param string $dirname
     * @param string $visibility
     * @throws WriteException
     */
    public function createDir($dirname, $visibility = Filesystem::VISIBILITY_DEFAULT);

    /**
     * Copy a directory.
     *
     * @param string $source
     * @param string $destination
     * @throws WriteException
     */
    public function copyDir($source, $destination);

    /**
     * Remove a directory.
     *
     * @param string $dirname
     * @throws WriteException
     */
    public function removeDir($dirname);

    /**
     * Erase a directory.
     *
     * @param string $dirname
     * @throws WriteException
     */
    public function eraseDir($dirname = '');
}
