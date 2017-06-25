<?php

namespace Kraken\Filesystem;

use Dazzle\Throwable\Exception\Runtime\ReadException;
use Dazzle\Throwable\Exception\Runtime\WriteException;
use Dazzle\Throwable\Exception\Logic\InstantiationException;
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
            throw new InstantiationException('Filesystem could not be initialized.', 0, $ex);
        }
        catch (Exception $ex)
        {
            throw new InstantiationException('Filesystem could not be initialized.', 0, $ex);
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
     * @override
     * @inheritDoc
     */
    public function exists($path)
    {
        try
        {
            return $this->fs->has($path);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ReadException("File $path does not exist.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isFile($path)
    {
        try
        {
            return $this->fs->getMetadata($path)['type'] === 'file';
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ReadException("Path $path could not be determined to be file or not.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isDir($path)
    {
        try
        {
            return $this->fs->getMetadata($path)['type'] === 'dir';
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ReadException("Path $path could not be determined to be directory or not.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getContents($directory = '', $recursive = false, $filterPatterns = [])
    {
        try
        {
            $contents = $this->fs->listContents($directory, $recursive);

            if (!$contents)
            {
                if (!$this->exists($directory))
                {
                    throw new ReadException("Directory $directory does not exist.");
                }

                return [];
            }

            $proxy = $this;
            $filterPatterns = (array) $filterPatterns;
            $filter = function($object) use($proxy, $filterPatterns) {
                foreach ($filterPatterns as $filterPattern)
                {
                    if (!$proxy->match($filterPattern, $object['basename']))
                    {
                        return false;
                    }
                }
                return true;
            };

            return array_values(array_filter($contents, $filter));
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ReadException("Directory $directory items could not be listed.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getFiles($directory = '', $recursive = false, $filterPatterns = [])
    {
        try
        {
            $contents = $this->fs->listContents($directory, $recursive);

            if (!$contents)
            {
                if (!$this->exists($directory))
                {
                    throw new ReadException("Directory $directory does not exist.");
                }

                return [];
            }

            $proxy = $this;
            $filterPatterns = (array) $filterPatterns;
            $filter = function($object) use($proxy, $filterPatterns) {
                if ($object['type'] !== 'file')
                {
                    return false;
                }
                foreach ($filterPatterns as $filterPattern)
                {
                    if (!$proxy->match($filterPattern, $object['basename']))
                    {
                        return false;
                    }
                }
                return true;
            };

            return array_values(array_filter($contents, $filter));
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ReadException("Directory $directory files could not be listed.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getDirectories($directory = '', $recursive = false, $filterPatterns = [])
    {
        try
        {
            $contents = $this->fs->listContents($directory, $recursive);

            if (!$contents)
            {
                if (!$this->exists($directory))
                {
                    throw new ReadException("Directory $directory does not exist.");
                }

                return [];
            }

            $proxy = $this;
            $filterPatterns = (array) $filterPatterns;
            $filter = function($object) use($proxy, $filterPatterns) {
                if ($object['type'] !== 'dir')
                {
                    return false;
                }
                foreach ($filterPatterns as $filterPattern)
                {
                    if (!$proxy->match($filterPattern, $object['basename']))
                    {
                        return false;
                    }
                }
                return true;
            };

            return array_values(array_filter($contents, $filter));
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ReadException("Directory $directory subdirectories could not be listed.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getVisibility($path = '')
    {
        try
        {
            return $this->fs->getVisibility($path);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ReadException("File $path visibility could not be determined.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isPublic($path = '')
    {
        return $this->getVisibility($path) === static::VISIBILITY_PUBLIC;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isPrivate($path = '')
    {
        return $this->getVisibility($path) === static::VISIBILITY_PRIVATE;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setVisibility($path, $visibility)
    {
        $ex = null;
        $result = false;

        try
        {
            $result = $this->fs->setVisibility($path, $visibility);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if (!$result || $ex !== null)
        {
            throw new WriteException("File $path visibility could not be set to $visibility.", 0, $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setPublic($path = '')
    {
        $this->setVisibility($path, static::VISIBILITY_PUBLIC);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function setPrivate($path = '')
    {
        $this->setVisibility($path, static::VISIBILITY_PRIVATE);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function create($path, $contents)
    {
        try
        {
            $this->fs->put($path, $contents);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("File $path could not be created.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function write($path, $contents)
    {
        try
        {
            $this->fs->update($path, $contents);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("File $path could not be overwritten.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function append($path, $contents)
    {
        try
        {
            $this->fs->update($path, $this->fs->read($path) . $contents);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("File $path could not be appeneded.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function prepend($path, $contents)
    {
        try
        {
            $this->fs->update($path, $contents . $this->fs->read($path));
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("File $path could not be prepended.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function read($path)
    {
        $ex = null;
        $ret = false;

        try
        {
            $ret = $this->fs->read($path);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ret === false || $ex !== null)
        {
            throw new ReadException("File $path could not be read.", 0, $ex);
        }

        return $ret;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function req($path)
    {
        $result = eval('?>' . $this->read($path));
        if (is_null($result))
        {
            $result = '';
        }

        return $result;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getSize($path)
    {
        try
        {
            $size = $this->fs->getSize($path);
            return $size === false ? 0 : $size;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ReadException("File $path size could not be determined.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getType($path)
    {
        try
        {
            return $this->fs->getMetadata($path)['type'];
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ReadException("File $path type could not be determined.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getMimetype($path)
    {
        try
        {
            return $this->fs->getMimetype($path);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ReadException("File $path mimetype could not be determined.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getTimestamp($path)
    {
        try
        {
            return $this->fs->getTimestamp($path);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new ReadException("File $path timestamp could not be determined.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function move($source, $destination)
    {
        $ex = null;
        $status = false;

        try
        {
            $status = $this->fs->rename($source, $destination);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if (!$status || $ex !== null)
        {
            throw new WriteException("File $source could not be moved to $destination.", 0, $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createFile($path, $contents = '', $visibility = self::VISIBILITY_DEFAULT)
    {
        try
        {
            $this->fs->put($path, $contents, $this->prepareConfig($visibility));
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("File $path could not be created.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function copyFile($source, $destination)
    {
        try
        {
            $this->fs->copy($source, $destination);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("File $source could not have been copied to $destination.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeFile($path)
    {
        try
        {
            $this->fs->delete($path);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("File $path could not be removed.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function eraseFile($path)
    {
        try
        {
            $this->fs->update($path, '');
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("File $path could not be erased.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createDir($dirname, $visibility = self::VISIBILITY_DEFAULT)
    {
        try
        {
            if ($this->exists($dirname))
            {
                $this->removeDir($dirname);
            }

            $this->fs->createDir($dirname, $this->prepareConfig($visibility));
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Directory $dirname could not be created.", 0, $ex);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function copyDir($source, $destination)
    {
        if ($this->exists($source) && !$this->isDir($source))
        {
            throw new WriteException("Directory $source could not be copied to $destination.");
        }

        if ($this->exists($destination))
        {
            throw new WriteException("Directory $source could not be copied to $destination.");
        }

        $this->ensuredCopyDir($source, $destination);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeDir($dirname)
    {
        if ($this->exists($dirname) && $this->isDir($dirname))
        {
            $this->ensuredRemoveDir($dirname);
            return;
        }

        throw new WriteException("Directory $dirname could not be removed.");
    }

    /**
     * @override
     * @inheritDoc
     */
    public function eraseDir($dirname = '')
    {
        if ($this->exists($dirname) && $this->isDir($dirname))
        {
            $this->ensuredEraseDir($dirname);
            return;
        }

        throw new WriteException("Directory $dirname could not be erased.");
    }

    /**
     * Return array config with visibility setting.
     *
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
     * Copy a directory.
     *
     * @param string $source
     * @param string $destination
     * @throws WriteException
     */
    private function ensuredCopyDir($source, $destination)
    {
        try
        {
            $filesList = $this->getContents($source);

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
                    $this->copyFile($path, $dest);
                }
            }

            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Directory $source could not be copied to $destination.");
    }

    /**
     * Remove a directory.
     *
     * @param string $dirname
     * @throws WriteException
     */
    private function ensuredRemoveDir($dirname)
    {
        try
        {
            $this->fs->deleteDir($dirname);
            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Directory $dirname could not be removed.", 0, $ex);
    }

    /**
     * Erase a directory.
     *
     * @param string $dirname
     * @throws WriteException
     */
    private function ensuredEraseDir($dirname)
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

            return;
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        throw new WriteException("Directory $dirname could not be erased.", 0, $ex);
    }

    /**
     * Try to match name using specified pattern.
     *
     * @param string $pattern
     * @param string $name
     * @return bool
     */
    private function match($pattern, $name)
    {
        return ($pattern !== '') ? (bool) preg_match($pattern, $name) : true;
    }
}
