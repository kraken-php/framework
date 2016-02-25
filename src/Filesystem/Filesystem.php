<?php

namespace Kraken\Filesystem;

use Kraken\Exception\Io\ReadException;
use Kraken\Exception\Io\WriteException;
use Kraken\Exception\Runtime\InstantiationException;
use League\Flysystem\AdapterInterface as LeagueAdapterInterface;
use League\Flysystem\Filesystem as LeagueFilesystem;
use League\Flysystem\FilesystemInterface as LeagueFilesystemInterface;
use Error;
use Exception;

class Filesystem implements FilesystemInterface
{
    /**
     * @var string
     */
    const VISIBILITY_DEFAULT = 'default';

    /**
     * @var string
     */
    const VISIBILITY_PUBLIC = LeagueAdapterInterface::VISIBILITY_PUBLIC;

    /**
     * @var string
     */
    const VISIBILITY_PRIVATE = LeagueAdapterInterface::VISIBILITY_PRIVATE;

    /**
     * @var string
     */
    const TYPE_DIRECTORY = 'dir';

    /**
     * @var string
     */
    const TYPE_FILE = 'file';

    /**
     * @var LeagueFilesystemInterface
     */
    protected $fs;

    /**
     * @param LeagueAdapterInterface $adapter
     * @throws InstantiationException
     */
    public function __construct(LeagueAdapterInterface $adapter)
    {
        try
        {
            $this->fs = new LeagueFilesystem($adapter);
        }
        catch (Error $ex)
        {
            throw new InstantiationException('Filesystem could not be initialized.', $ex);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException('Filesystem could not be initialized.', $ex);
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->fs);
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
        try
        {
            return $this->fs->has($path);
        }
        catch (Error $ex)
        {
            throw new ReadException("File $path does not exist.", $ex);
        }
        catch (Exception $ex)
        {
            throw new ReadException("File $path does not exist.", $ex);
        }
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
        try
        {
            $this->fs->rename($source, $destination);
        }
        catch (Error $ex)
        {
            throw new WriteException("File $source could not be moved to $destination.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("File $source could not be moved to $destination.", $ex);
        }
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
        try
        {
            return $this->fs->getMetadata($path)['type'] === 'file';
        }
        catch (Error $ex)
        {
            throw new ReadException("Path $path could not be determined to be file or not.", $ex);
        }
        catch (Exception $ex)
        {
            throw new ReadException("Path $path could not be determined to be file or not.", $ex);
        }
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
        try
        {
            return $this->fs->getMetadata($path)['type'] === 'dir';
        }
        catch (Error $ex)
        {
            throw new ReadException("Path $path could not be determined to be directory or not.", $ex);
        }
        catch (Exception $ex)
        {
            throw new ReadException("Path $path could not be determined to be directory or not.", $ex);
        }
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
        try
        {
            $contents = $this->fs->listContents($directory, $recursive);

            $proxy = $this;
            $filter = function($object) use($proxy, $filterPattern) {
                return $proxy->match($filterPattern, $object['basename']);
            };

            return array_values(array_filter($contents, $filter));
        }
        catch (Error $ex)
        {
            throw new ReadException("Directory $directory items could not be listed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new ReadException("Directory $directory items could not be listed.", $ex);
        }
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
        try
        {
            $contents = $this->fs->listContents($directory, $recursive);

            $proxy = $this;
            $filter = function($object) use($proxy, $filterPattern) {
                return $object['type'] === 'file' && $proxy->match($filterPattern, $object['basename']);
            };

            return array_values(array_filter($contents, $filter));
        }
        catch (Error $ex)
        {
            throw new ReadException("Directory $directory files could not be listed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new ReadException("Directory $directory files could not be listed.", $ex);
        }
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
        try
        {
            $contents = $this->fs->listContents($directory, $recursive);

            $proxy = $this;
            $filter = function($object) use($proxy, $filterPattern) {
                return $object['type'] === 'dir' && $proxy->match($filterPattern, $object['basename']);
            };

            return array_values(array_filter($contents, $filter));
        }
        catch (Error $ex)
        {
            throw new ReadException("Directory $directory subdirectories could not be listed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new ReadException("Directory $directory subdirectories could not be listed.", $ex);
        }
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
        try
        {
            return $this->fs->getVisibility($path);
        }
        catch (Error $ex)
        {
            throw new ReadException("File $path visibility could not be determined.", $ex);
        }
        catch (Exception $ex)
        {
            throw new ReadException("File $path visibility could not be determined.", $ex);
        }
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
        return $this->visibility($path) === static::VISIBILITY_PUBLIC;
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
        return $this->visibility($path) === static::VISIBILITY_PRIVATE;
    }

    /**
     * Sets visibility of file or directory to public.
     *
     * @param string $path
     * @throws WriteException
     */
    public function setPublic($path = '')
    {
        if (!$this->fs->setVisibility($path, static::VISIBILITY_PUBLIC))
        {
            throw new WriteException("File $path visibility could not be set to public.");
        }
    }

    /**
     * Sets visibility of file or directory to private.
     *
     * @param string $path
     * @throws WriteException
     */
    public function setPrivate($path = '')
    {
        if (!$this->fs->setVisibility($path, static::VISIBILITY_PRIVATE))
        {
            throw new WriteException("File $path visibility could not be set to private.");
        }
    }

    /**
     * Create a new file.
     *
     * @param string $path
     * @param string $contents
     * @param string $visibility
     * @throws WriteException
     */
    public function create($path, $contents = '', $visibility = self::VISIBILITY_DEFAULT)
    {
        try
        {
            $this->fs->write($path, $contents, $this->prepareConfig($visibility));
        }
        catch (Error $ex)
        {
            throw new WriteException("File $path could not be created.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("File $path could not be created.", $ex);
        }
    }

    /**
     * Create a file or update if exists.
     *
     * @param string $path
     * @param string $contents
     * @param string $visibility
     * @throws WriteException
     */
    public function write($path, $contents = '', $visibility = self::VISIBILITY_DEFAULT)
    {
        try
        {
            $this->fs->put($path, $contents, $this->prepareConfig($visibility));
        }
        catch (Error $ex)
        {
            throw new WriteException("File $path could not be overwritten.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("File $path could not be overwritten.", $ex);
        }
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
        try
        {
            $this->fs->update($path, $this->fs->read($path) . $contents);
        }
        catch (Error $ex)
        {
            throw new WriteException("File $path could not be appeneded.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("File $path could not be appeneded.", $ex);
        }
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
        try
        {
            $this->fs->update($path, $contents . $this->fs->read($path));
        }
        catch (Error $ex)
        {
            throw new WriteException("File $path could not be prepended.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("File $path could not be prepended.", $ex);
        }
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
        if (($ret = $this->fs->read($path)) === false)
        {
            throw new ReadException("File $path could not be read.");
        }

        return $ret;
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
        return "data://text/plain;base64," . base64_encode($this->read($path));
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
        try
        {
            $this->fs->copy($source, $destination);
        }
        catch (Error $ex)
        {
            throw new WriteException("File $source could not have benn copied to $destination.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("File $source could not have benn copied to $destination.", $ex);
        }
    }

    /**
     * Remove a file.
     *
     * @param string $path
     * @throws WriteException
     */
    public function remove($path)
    {
        try
        {
            $this->fs->delete($path);
        }
        catch (Error $ex)
        {
            throw new WriteException("File $path could not be removed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("File $path could not be removed.", $ex);
        }
    }

    /**
     * Erase a file.
     *
     * @param string $path
     * @throws WriteException
     */
    public function erase($path)
    {
        try
        {
            $this->fs->update($path, '');
        }
        catch (Error $ex)
        {
            throw new WriteException("File $path could not be erased.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("File $path could not be erased.", $ex);
        }
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
        try
        {
            return $this->fs->getSize($path);
        }
        catch (Error $ex)
        {
            throw new ReadException("File $path size could not be determined.", $ex);
        }
        catch (Exception $ex)
        {
            throw new ReadException("File $path size could not be determined.", $ex);
        }
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
        try
        {
            return $this->fs->getMetadata($path)['type'];
        }
        catch (Error $ex)
        {
            throw new ReadException("File $path type could not be determined.", $ex);
        }
        catch (Exception $ex)
        {
            throw new ReadException("File $path type could not be determined.", $ex);
        }
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
        try
        {
            return $this->fs->getMimetype($path);
        }
        catch (Error $ex)
        {
            throw new ReadException("File $path mimetype could not be determined.", $ex);
        }
        catch (Exception $ex)
        {
            throw new ReadException("File $path mimetype could not be determined.", $ex);
        }
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
        try
        {
            return $this->fs->getTimestamp($path);
        }
        catch (Error $ex)
        {
            throw new ReadException("File $path timestamp could not be determined.", $ex);
        }
        catch (Exception $ex)
        {
            throw new ReadException("File $path timestamp could not be determined.", $ex);
        }
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
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Create a directory.
     *
     * @param string $dirname
     * @param string $visibility
     * @throws WriteException
     */
    public function createDir($dirname, $visibility = self::VISIBILITY_DEFAULT)
    {
        try
        {
            $this->fs->createDir($dirname, $this->prepareConfig($visibility));
        }
        catch (Error $ex)
        {
            throw new WriteException("Directory $dirname could not be created.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Directory $dirname could not be created.", $ex);
        }
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
        $filesList = $this->contents($source);

        foreach ($filesList as $file)
        {
            $path = $file['path'];
            $dest = explode(DIRECTORY_SEPARATOR, $path, 2);
            $dest = $destination . DIRECTORY_SEPARATOR . $dest[1];

            if ($file['type'] === Filesystem::TYPE_DIRECTORY)
            {
                $this->copyDir($path, $dest);
            }
            else if ($file['type'] === Filesystem::TYPE_FILE)
            {
                $this->copy($path, $dest);
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
        try
        {
            $this->fs->deleteDir($dirname);
        }
        catch (Error $ex)
        {
            throw new WriteException("Directory $dirname could not be removed.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Directory $dirname could not be removed.", $ex);
        }
    }

    /**
     * Erase a directory.
     *
     * @param string $dirname
     * @throws WriteException
     */
    public function eraseDir($dirname = '')
    {
        try
        {
            $listing = $this->fs->listContents($dirname, false);

            foreach ($listing as $item)
            {
                if ($item['type'] === 'dir')
                {
                    $this->fs->deleteDir($item['path']);
                }
                else
                {
                    $this->fs->delete($item['path']);
                }
            }
        }
        catch (Error $ex)
        {
            throw new WriteException("Directory $dirname could not be erased.", $ex);
        }
        catch (Exception $ex)
        {
            throw new WriteException("Directory $dirname could not be erased.", $ex);
        }
    }

    /**
     * @param $visibility
     * @return string[]
     */
    protected function prepareConfig($visibility)
    {
        if ($visibility === static::VISIBILITY_DEFAULT)
        {
            return [];
        }

        return [
            'visibility' => $visibility
        ];
    }

    /**
     * @param string $pattern
     * @param string $name
     * @return bool
     */
    protected function match($pattern, $name)
    {
        return ($pattern !== '') ? (bool) preg_match($pattern, $name) : true;
    }
}
