<?php

namespace Kraken\Filesystem;

use Kraken\Throwable\Io\ReadException;
use Kraken\Throwable\Io\WriteException;

class FilesystemManager implements FilesystemManagerInterface
{
    /**
     * @var FilesystemInterface[]
     */
    protected $filesystems;

    /**
     * @param FilesystemInterface[] $filesystems
     */
    public function __construct($filesystems = [])
    {
        $this->mountFilesystems($filesystems);
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->filesystems);
    }

    /**
     * Mount collection of FilesystemInterfaces
     *
     * @param FilesystemInterface[] $filesystems
     */
    public function mountFilesystems($filesystems)
    {
        foreach ($filesystems as $prefix=>$filesystem)
        {
            $this->mountFilesystem($prefix, $filesystem);
        }
    }

    /**
     * Check if there is a FilesystemInterface saved under $prefix key
     *
     * @param string $prefix
     * @return bool
     */
    public function existsFilesystem($prefix)
    {
        return isset($this->filesystems[$prefix]);
    }

    /**
     * Mount FilesystemInterface $filesystem under $prefix key
     *
     * @param string $prefix
     * @param FilesystemInterface $filesystem
     */
    public function mountFilesystem($prefix, FilesystemInterface $filesystem)
    {
        $this->filesystems[$prefix] = $filesystem;
    }

    /**
     * Unmount FilesystemInterface saved under $prefix key
     *
     * @param string $prefix
     */
    public function unmountFilesystem($prefix)
    {
        unset($this->filesystems[$prefix]);
    }

    /**
     * Return FilesystemInterface saved under $prefix key or null if it does not exist
     *
     * @param string $prefix
     * @return FilesystemInterface
     */
    public function getFilesystem($prefix)
    {
        if (!$this->existsFilesystem($prefix))
        {
            return null;
        }

        return $this->filesystems[$prefix];
    }

    /**
     * Filter $path in search for $prefix, if its present explode $path into $prefix and $newPath
     *
     * @param string $path
     * @return string[]
     * @throws ReadException
     */
    public function filterPrefix($path)
    {
        if (!preg_match('#^.+\:\/\/.*#', $path))
        {
            throw new ReadException("No prefix detected in [$path].");
        }

        list($prefix, $newPath) = explode('://', $path, 2);

        return [ $prefix, $newPath ];
    }

    /**
     * Check whether a file or directory exist.
     *
     * @param string $path
     * @return bool
     * @throws ReadException
     */
    public function exists($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->exists($path);
    }

    /**
     * Move (rename) a file or directory.
     *
     * @param string $source
     * @param string $destination
     * @throws WriteException
     */
    public function move($source, $destination)
    {
        list($sourcePrefix, $sourcePath) = $this->filterPrefix($source);
        list($destPrefix, $destPath) = $this->filterPrefix($destination);

        if (($sourceFs = $this->getFilesystem($sourcePrefix)) === null || ($destFs = $this->getFilesystem($destPrefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$sourcePrefix].");
        }

        $destFs->write($destPath, $sourceFs->read($sourcePath));
        $sourceFs->remove($sourcePath);
    }

    /**
     * Check if path is a file.
     *
     * @param string $path
     * @return bool
     * @throws ReadException
     */
    public function isFile($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->isFile($path);
    }

    /**
     * Check if path is a file.
     *
     * @param string $path
     * @return bool
     * @throws ReadException
     */
    public function isDir($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->isDir($path);
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     * @param string $filterPattern
     * @return array
     * @throws ReadException
     */
    public function contents($directory = '', $recursive = false, $filterPattern = '')
    {
        list($prefix, $directory) = $this->filterPrefix($directory);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->contents($directory, $recursive, $filterPattern);
    }

    /**
     * List files of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     * @param string $filterPattern
     * @return array
     * @throws ReadException
     */
    public function files($directory = '', $recursive = false, $filterPattern = '')
    {
        list($prefix, $directory) = $this->filterPrefix($directory);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->files($directory);
    }

    /**
     * List directories of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     * @param string $filterPattern
     * @return array
     * @throws ReadException
     */
    public function directories($directory = '', $recursive = false, $filterPattern = '')
    {
        list($prefix, $directory) = $this->filterPrefix($directory);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->directories($directory, $recursive, $filterPattern);
    }

    /**
     * Get visibility of a file or directory.
     *
     * @param string $path
     * @return string
     * @throws ReadException
     */
    public function visibility($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->visibility($path);
    }

    /**
     * Check if file or directory is public.
     *
     * @param string $path
     * @return bool
     * @throws ReadException
     */
    public function isPublic($path = '')
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->isPublic($path);
    }

    /**
     * Check if file or directory is private.
     *
     * @param string $path
     * @return bool
     * @throws ReadException
     */
    public function isPrivate($path = '')
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->isPrivate($path);
    }

    /**
     * Sets visibility of file or directory to public.
     *
     * @param string $path
     * @throws WriteException
     */
    public function setPublic($path = '')
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->setPublic($path);
    }

    /**
     * Sets visibility of file or directory to private.
     *
     * @param string $path
     * @throws WriteException
     */
    public function setPrivate($path = '')
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->setPrivate($path);
    }

    /**
     * Create a new file.
     *
     * @param string $path
     * @param string $contents
     * @param string $visibility
     * @throws WriteException
     */
    public function create($path, $contents = '', $visibility = Filesystem::VISIBILITY_DEFAULT)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->create($path, $contents, $visibility);
    }

    /**
     * Create a file or update if exists.
     *
     * @param string $path
     * @param string $contents
     * @param string $visibility
     * @throws WriteException
     */
    public function write($path, $contents = '', $visibility = Filesystem::VISIBILITY_DEFAULT)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->write($path, $contents, $visibility);
    }

    /**
     * Appends contents to file.
     *
     * @param string $path
     * @param string $contents
     * @throws WriteException
     */
    public function append($path, $contents)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->append($path, $contents);
    }

    /**
     * Prepends contents to file.
     *
     * @param string $path
     * @param string $contents
     * @throws WriteException
     */
    public function prepend($path, $contents)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->prepend($path, $contents);
    }

    /**
     * Read a file.
     *
     * @param string $path
     * @return string
     * @throws ReadException
     */
    public function read($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->read($path);
    }

    /**
     * Require a file.
     *
     * @param string $path
     * @return mixed
     * @throws ReadException
     */
    public function req($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->req($path);
    }

    /**
     * Copy a file.
     *
     * @param string $source
     * @param string $destination
     * @throws WriteException
     */
    public function copy($source, $destination)
    {
        list($sourcePrefix, $sourcePath) = $this->filterPrefix($source);
        list($destPrefix, $destPath) = $this->filterPrefix($destination);

        if (($sourceFs = $this->getFilesystem($sourcePrefix)) === null || ($destFs = $this->getFilesystem($destPrefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$sourcePrefix].");
        }

        $destFs->write($destPath, $sourceFs->read($sourcePath));
    }

    /**
     * Remove a file.
     *
     * @param string $path
     * @throws WriteException
     */
    public function remove($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->remove($path);
    }

    /**
     * Erase a file.
     *
     * @param string $path
     * @throws WriteException
     */
    public function erase($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->erase($path);
    }

    /**
     * Returns size of a file.
     *
     * @param $path
     * @return int
     * @throws ReadException
     */
    public function size($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->size($path);
    }

    /**
     * Get type of a file.
     *
     * @param string $path
     * @return string
     * @throws ReadException
     */
    public function type($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->type($path);
    }

    /**
     * Get mimetype of a file.
     *
     * @param string $path
     * @return string
     * @throws ReadException
     */
    public function mimetype($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->mimetype($path);
    }

    /**
     * Get timestamp of a file.
     *
     * @param string $path
     * @return string
     * @throws ReadException
     */
    public function timestamp($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->timestamp($path);
    }

    /**
     * Get extension of a file.
     *
     * @param string $path
     * @return string
     * @throws ReadException
     */
    public function extension($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->extension($path);
    }

    /**
     * Create a directory.
     *
     * @param string $dirname
     * @param string $visibility
     * @throws WriteException
     */
    public function createDir($dirname, $visibility = Filesystem::VISIBILITY_DEFAULT)
    {
        list($prefix, $dirname) = $this->filterPrefix($dirname);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->createDir($dirname, $visibility);
    }

    /**
     * Copy a directory.
     *
     * @param string $source
     * @param string $destination
     * @throws WriteException
     */
    public function copyDir($source, $destination)
    {
        list($sourcePrefix, $sourcePath) = $this->filterPrefix($source);
        list($destPrefix, $destPath) = $this->filterPrefix($destination);

        if (($sourceFs = $this->getFilesystem($sourcePrefix)) === null || ($destFs = $this->getFilesystem($destPrefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$sourcePrefix].");
        }

        $filesList = $sourceFs->contents($sourcePath, true);

        foreach ($filesList as $file)
        {
            $path = $file['path'];
            $dest = explode(DIRECTORY_SEPARATOR, $path, 2);
            $dest = $destPath . DIRECTORY_SEPARATOR . $dest[1];

            if ($file['type'] === Filesystem::TYPE_DIRECTORY)
            {
                $destFs->createDir($dest);
            }
            else if ($file['type'] === Filesystem::TYPE_FILE)
            {
                $destFs->write($destPath, $sourceFs->read($sourcePath));
            }
        }
    }

    /**
     * Remove a directory.
     *
     * @param string $dirname
     * @throws WriteException
     */
    public function removeDir($dirname)
    {
        list($prefix, $dirname) = $this->filterPrefix($dirname);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->removeDir($dirname);
    }

    /**
     * Erase a directory.
     *
     * @param string $dirname
     * @throws WriteException
     */
    public function eraseDir($dirname = '')
    {
        list($prefix, $dirname) = $this->filterPrefix($dirname);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->eraseDir($dirname);
    }
}
