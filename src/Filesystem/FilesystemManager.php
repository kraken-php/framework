<?php

namespace Kraken\Filesystem;

use Kraken\Throwable\Exception\Runtime\ReadException;
use Kraken\Throwable\Exception\Runtime\WriteException;
use Error;
use Exception;

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
     * @override
     * @inheritDoc
     */
    public function mountFilesystems($filesystems)
    {
        foreach ($filesystems as $prefix=>$filesystem)
        {
            $this->mountFilesystem($prefix, $filesystem);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function existsFilesystem($prefix)
    {
        return isset($this->filesystems[$prefix]);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function mountFilesystem($prefix, FilesystemInterface $filesystem)
    {
        $this->filesystems[$prefix] = $filesystem;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function unmountFilesystem($prefix)
    {
        unset($this->filesystems[$prefix]);
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function getContents($directory = '', $recursive = false, $filterPattern = '')
    {
        list($prefix, $directory) = $this->filterPrefix($directory);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->getContents($directory, $recursive, $filterPattern);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getFiles($directory = '', $recursive = false, $filterPattern = '')
    {
        list($prefix, $directory) = $this->filterPrefix($directory);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->getFiles($directory, $recursive, $filterPattern);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getDirectories($directory = '', $recursive = false, $filterPattern = '')
    {
        list($prefix, $directory) = $this->filterPrefix($directory);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->getDirectories($directory, $recursive, $filterPattern);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getVisibility($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->getVisibility($path);
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function setVisibility($path, $visibility)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->setVisibility($path, $visibility);
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function create($path, $contents)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->create($path, $contents);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function write($path, $contents)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->write($path, $contents);
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function getSize($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->getSize($path);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getType($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->getType($path);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getMimetype($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->getMimetype($path);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getTimestamp($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new ReadException("No filesystem saved under prefix [$prefix].");
        }

        return $fs->getTimestamp($path);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function move($source, $destination)
    {
        list($sourcePrefix, $sourcePath) = $this->filterPrefix($source);
        list($destPrefix, $destPath) = $this->filterPrefix($destination);

        if (($sourceFs = $this->getFilesystem($sourcePrefix)) === null || ($destFs = $this->getFilesystem($destPrefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$sourcePrefix].");
        }

        try
        {
            if ($sourceFs->isFile($sourcePath))
            {
                $this->copyFile($source, $destination);
                $sourceFs->removeFile($sourcePath);
            }
            else
            {
                $this->copyDir($source, $destination);
                $sourceFs->removeDir($sourcePath);
            }

            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Move operation from [$source] to [$destination] could not be completed.", $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createFile($path, $contents = '', $visibility = Filesystem::VISIBILITY_DEFAULT)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->createFile($path, $contents, $visibility);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function copyFile($source, $destination)
    {
        list($sourcePrefix, $sourcePath) = $this->filterPrefix($source);
        list($destPrefix, $destPath) = $this->filterPrefix($destination);

        if (($sourceFs = $this->getFilesystem($sourcePrefix)) === null || ($destFs = $this->getFilesystem($destPrefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$sourcePrefix].");
        }

        if (!$sourceFs->exists($sourcePath) || !$sourceFs->isFile($sourcePath) || $destFs->exists($destPath))
        {
            throw new WriteException("Could not copy $source.");
        }

        $destFs->createFile($destPath, $sourceFs->read($sourcePath));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeFile($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->removeFile($path);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function eraseFile($path)
    {
        list($prefix, $path) = $this->filterPrefix($path);

        if (($fs = $this->getFilesystem($prefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$prefix].");
        }

        $fs->eraseFile($path);
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
     */
    public function copyDir($source, $destination)
    {
        list($sourcePrefix, $sourcePath) = $this->filterPrefix($source);
        list($destPrefix, $destPath) = $this->filterPrefix($destination);

        if (($sourceFs = $this->getFilesystem($sourcePrefix)) === null || ($destFs = $this->getFilesystem($destPrefix)) === null)
        {
            throw new WriteException("No filesystem saved under prefix [$sourcePrefix].");
        }

        if (!$sourceFs->exists($sourcePath) || !$sourceFs->isDir($sourcePath) || $destFs->exists($destPath))
        {
            throw new WriteException("Could not copy $source.");
        }

        $filesList = $sourceFs->getContents($sourcePath, true);

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
                $destFs->createFile($dest, $sourceFs->read($path));
            }
        }
    }

    /**
     * @override
     * @inheritDoc
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
     * @override
     * @inheritDoc
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

    /**
     * Filter $path in search for $prefix, if its present explode $path into $prefix and $newPath.
     *
     * @param string $path
     * @return string[]
     * @throws ReadException
     */
    private function filterPrefix($path)
    {
        if (!preg_match('#^.+\:\/\/.*#', $path))
        {
            throw new ReadException("No prefix detected in [$path].");
        }

        list($prefix, $newPath) = explode('://', $path, 2);

        return [ $prefix, $newPath ];
    }
}
