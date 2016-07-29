<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;

trait FsApiExistsPartial
{
    /**
     * @see TestCase::getTest
     * @return TModule
     */
    abstract public function getTest();

    /**
     * @return string
     */
    abstract public function getPrefix();

    /**
     * @param string $path
     * @param null $replace
     * @param string $with
     * @return string
     */
    abstract public function getPrefixed($path, $replace = null, $with = '');

    /**
     * @param string $path
     * @param null $replace
     * @param string $with
     * @return string
     */
    abstract public function getPath($path, $replace = null, $with = '');

    /**
     * @param string $path
     * @param string $typeFilter
     * @param bool $recursive
     * @param string $filePattern
     * @return array
     */
    abstract public function getPathData($path = '', $typeFilter = '', $recursive = false, $filePattern = '');

    /**
     * @return FilesystemInterface
     */
    abstract public function createFilesystem();

    /**
     *
     */
    public function testApiExists_ReturnsTrue_ForExistingFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertTrue($fs->exists($this->getPrefixed('FILE_A')));
    }

    /**
     *
     */
    public function testApiExists_ReturnsTrue_ForExistingDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertTrue($fs->exists($this->getPrefixed('DIR_A')));
    }

    /**
     *
     */
    public function testApiExists_ReturnsFalse_ForNonExistingNode()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertFalse($fs->exists($this->getPrefixed('NULL')));
    }
}
