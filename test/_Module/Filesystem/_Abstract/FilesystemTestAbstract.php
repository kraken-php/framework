<?php

namespace Kraken\_Module\Filesystem\_Abstract;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;

abstract class FilesystemTestAbstract extends TModule
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string[]
     */
    protected $paths;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->path = realpath(__DIR__ . '/..') . '/_Dir';
        $this->paths = [
            'DIR_A'     => '/DIR_A',
            'DIR_B'     => '/DIR_B',
            'DIR_C'     => '/DIR_B/DIR_C',
            'DIR_D'     => '/DIR_D',
            'FILE_A'    => '/FILE_A',
            'FILE_B'    => '/FILE_B.txt',
            'FILE_C'    => '/DIR_A/FILE_C',
            'FILE_D'    => 'FILE_D',
            'NULL'      => '/NULL',
            'DIR_NULL'  => '/DIR_NULL',
            'FILE_NULL' => '/FILE_NULL'
        ];

        $this->createDirStructure();
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->destroyDirStructure();

        parent::tearDown();
    }

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
        return $this->getPath($path, $replace, $with);
    }

    /**
     * @param string $path
     * @param null $replace
     * @param string $with
     * @return string
     */
    public function getFullPath($path, $replace = null, $with = '')
    {
        return $this->path . $this->getPath($path, $replace, $with);
    }

    /**
     * @param string $path
     * @param null $replace
     * @param string $with
     * @return string
     */
    public function getPath($path, $replace = null, $with = '')
    {
        if (!isset($this->paths[$path]))
        {
            return $path;
        }

        $path = $this->paths[$path];

        if ($replace !== null)
        {
            $path = str_replace($replace, $with, $path);
        }

        return $path;
    }

    /**
     * @param string $path
     * @param string $typeFilter
     * @param bool $recursive
     * @param string $filePattern
     * @return array
     */
    public function getPathData($path = '', $typeFilter = '', $recursive = false, $filePattern = '')
    {
        $path = isset($this->paths[$path]) ? $this->paths[$path] : $path;
        $path = $this->path . $path;

        $filePatterns = (array) $filePattern;
        $filter = function($object) use($typeFilter, $filePatterns) {
            foreach ($filePatterns as $filePattern)
            {
                if ($filePattern !== '' && !preg_match($filePattern, $object['basename']))
                {
                    return false;
                }
            }

            return ($typeFilter === '' || $typeFilter === $object['type']);
        };

        $data = $this->getPathAllData($path, $recursive);

        return array_values(array_filter($data, $filter));
    }

    /**
     * @return FilesystemInterface
     */
    public function createFilesystem($path = null)
    {
        return null;
    }

    /**
     *
     */
    private function createDirStructure()
    {
        $this->destroyDirStructure();
        $path = $this->path;

        mkdir($path);
        mkdir($path . '/DIR_A');
        mkdir($path . '/DIR_B');
        mkdir($path . '/DIR_A/DIR_C');
        mkdir($path . '/DIR_D');
        file_put_contents($path . '/FILE_A', 'FILE_A_TEXT');
        file_put_contents($path . '/FILE_B.txt', 'FILE_B_TEXT');
        file_put_contents($path . '/DIR_A/FILE_C', 'FILE_C_TEXT');
        file_put_contents($path . '/FILE_D', 'FILE_D_TEXT');
        file_put_contents($path . '/FILE_E', '<?php return "FILE_E_TEXT";');
        chmod($path . '/DIR_D',  0700);
        chmod($path . '/FILE_D', 0700);
    }

    /**
     *
     */
    private function destroyDirStructure()
    {
        $this->rrmdir($this->path);
    }

    /**
     * @param string $path
     * @param bool $recursive
     * @return array
     */
    private function getPathAllData($path, $recursive)
    {
        $data = [];
        $objects = scandir($path);

        foreach ($objects as $object)
        {
            $local = $path . '/' . $object;

            if ($object != "." && $object != "..")
            {
                $data[] = $this->getLocalData($local);

                if (is_dir($local) && $recursive)
                {
                    $results = $this->getPathAllData($local, $recursive);

                    foreach ($results as $result)
                    {
                        $data[] = $result;
                    }

                    unset($results);
                }
            }
        }

        return $data;
    }

    /**
     * @param $path
     * @return mixed[]
     */
    private function getLocalData($path)
    {
        $isDir = is_dir($path);
        $local = ltrim(str_replace($this->path, '', $path), '/');
        $dirname = dirname('./' . $local . '/');
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $record = array_merge([], [
            'type'      => $isDir ? 'dir' : 'file',
            'path'      => $local,
            'timestamp' => filemtime($path)
        ]);

        if (!$isDir)
        {
            $record = array_merge($record, [
                'size'  => filesize($path)
            ]);
        }

        $record = array_merge($record, [
            'dirname'   => $dirname === '.' ? '' : ltrim($dirname, "./"),
            'basename'  => basename($path)
        ]);

        if ($ext !== '')
        {
            $record = array_merge($record, [
                'extension' => $ext
            ]);
        }

        $record = array_merge($record, [
            'filename'  => str_replace('.' . $ext, '', basename($path))
        ]);

        return $record;
    }

    /**
     * @param $dir
     */
    private function rrmdir($dir)
    {
        if (is_dir($dir))
        {
            $objects = scandir($dir);

            foreach ($objects as $object)
            {
                if ($object != "." && $object != "..")
                {
                    if (is_dir($dir."/".$object))
                    {
                        $this->rrmdir($dir . "/" . $object);
                    }
                    else
                    {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}
