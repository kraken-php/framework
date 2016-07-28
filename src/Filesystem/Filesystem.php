<?php

namespace Kraken\Filesystem;

use Kraken\Throwable\Exception\Runtime\Io\IoReadException;
use Kraken\Throwable\Exception\Runtime\Io\IoWriteException;
use Kraken\Throwable\Exception\Logic\InstantiationException;
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
     * @override
     * @inheritDoc
     */
    public function exists($path)
    {
        $ex = null;

        try
        {
            return $this->fs->has($path);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoReadException("File $path does not exist.", $ex);
        }
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
            throw new IoWriteException("File $source could not be moved to $destination.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isFile($path)
    {
        $ex = null;

        try
        {
            return $this->fs->getMetadata($path)['type'] === 'file';
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoReadException("Path $path could not be determined to be file or not.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function isDir($path)
    {
        $ex = null;

        try
        {
            return $this->fs->getMetadata($path)['type'] === 'dir';
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoReadException("Path $path could not be determined to be directory or not.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getContents($directory = '', $recursive = false, $filterPattern = '')
    {
        $ex = null;

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
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoReadException("Directory $directory items could not be listed.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getFiles($directory = '', $recursive = false, $filterPattern = '')
    {
        $ex = null;

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
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoReadException("Directory $directory files could not be listed.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getDirectories($directory = '', $recursive = false, $filterPattern = '')
    {
        $ex = null;

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
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoReadException("Directory $directory subdirectories could not be listed.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getVisibility($path = '')
    {
        $ex = null;

        try
        {
            return $this->fs->getVisibility($path);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoReadException("File $path visibility could not be determined.", $ex);
        }
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
            throw new IoWriteException("File $path visibility could not be set to $visibility.", $ex);
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
    public function create($path, $contents = '', $visibility = self::VISIBILITY_DEFAULT)
    {
        $ex = null;

        try
        {
            $this->fs->write($path, $contents, $this->prepareConfig($visibility));
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoWriteException("File $path could not be created.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function write($path, $contents = '', $visibility = self::VISIBILITY_DEFAULT)
    {
        $ex = null;

        try
        {
            $this->fs->put($path, $contents, $this->prepareConfig($visibility));
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoWriteException("File $path could not be overwritten.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function append($path, $contents)
    {
        $ex = null;

        try
        {
            $this->fs->update($path, $this->fs->read($path) . $contents);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoWriteException("File $path could not be appeneded.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function prepend($path, $contents)
    {
        $ex = null;

        try
        {
            $this->fs->update($path, $contents . $this->fs->read($path));
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoWriteException("File $path could not be prepended.", $ex);
        }
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
            throw new IoReadException("File $path could not be read.", $ex);
        }

        return $ret;
    }

    /**
     * @override
     * @inheritDoc
     */
    public function req($path)
    {
        return "data://text/plain;base64," . base64_encode($this->read($path));
    }

    /**
     * @override
     * @inheritDoc
     */
    public function copy($source, $destination)
    {
        $ex = null;

        try
        {
            $this->fs->copy($source, $destination);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoWriteException("File $source could not have benn copied to $destination.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function remove($path)
    {
        $ex = null;

        try
        {
            $this->fs->delete($path);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoWriteException("File $path could not be removed.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function erase($path)
    {
        $ex = null;

        try
        {
            $this->fs->update($path, '');
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoWriteException("File $path could not be erased.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getSize($path)
    {
        $ex = null;

        try
        {
            return $this->fs->getSize($path);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoReadException("File $path size could not be determined.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getType($path)
    {
        $ex = null;

        try
        {
            return $this->fs->getMetadata($path)['type'];
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoReadException("File $path type could not be determined.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getMimetype($path)
    {
        $ex = null;

        try
        {
            return $this->fs->getMimetype($path);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoReadException("File $path mimetype could not be determined.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getTimestamp($path)
    {
        $ex = null;

        try
        {
            return $this->fs->getTimestamp($path);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoReadException("File $path timestamp could not be determined.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function getExtension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * @override
     * @inheritDoc
     */
    public function createDir($dirname, $visibility = self::VISIBILITY_DEFAULT)
    {
        $ex = null;

        try
        {
            $this->fs->createDir($dirname, $this->prepareConfig($visibility));
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoWriteException("Directory $dirname could not be created.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function copyDir($source, $destination)
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
                $this->copy($path, $dest);
            }
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function removeDir($dirname)
    {
        $ex = null;

        try
        {
            $this->fs->deleteDir($dirname);
        }
        catch (Error $ex)
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoWriteException("Directory $dirname could not be removed.", $ex);
        }
    }

    /**
     * @override
     * @inheritDoc
     */
    public function eraseDir($dirname = '')
    {
        $ex = null;

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
        {}
        catch (Exception $ex)
        {}

        if ($ex !== null)
        {
            throw new IoWriteException("Directory $dirname could not be erased.", $ex);
        }
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
