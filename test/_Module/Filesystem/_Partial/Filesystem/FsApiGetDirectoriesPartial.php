<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Dazzle\Throwable\Exception\Runtime\ReadException;

trait FsApiGetDirectoriesPartial
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
    public function testApiGetDirectories_ReturnsContentsOnRoot_WhenNoDirectorySet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        if ($this->getPrefix() !== '')
        {
            return; // Fs behaviour only!
        }

        $test->assertSame($this->getPathData('', 'dir'), $fs->getDirectories());
    }

    /**
     *
     */
    public function testApiGetDirectories_ReturnsContentsOnRoot_WhenEmptyStringSet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData('', 'dir'), $fs->getDirectories($this->getPrefixed('')));
    }

    /**
     *
     */
    public function testApiGetDirectories_ReturnsContentsOnRoot_WhenRootStringSet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData('', 'dir'), $fs->getDirectories($this->getPrefixed('/')));
    }

    /**
     *
     */
    public function testApiGetDirectories_ReturnsContents_WhenPathDoesExistAndIsValid()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData('DIR_A', 'dir'), $fs->getDirectories($this->getPrefixed('DIR_A')));
    }

    /**
     *
     */
    public function testApiGetDirectories_ReturnsEmptyArray_WhenPathDoesExistButIsNotValid()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame([], $fs->getDirectories($this->getPrefixed('FILE_A')));
    }

    /**
     *
     */
    public function testApiGetDirectories_ReturnsEmptyArray_WhenDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->setExpectedException(ReadException::class);
        $fs->getDirectories($this->getPrefixed('DIR_NULL'));
    }

    /**
     *
     */
    public function testApiGetDirectories_ReturnsContents_WhenFlagIsRecursive()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData('', 'dir', true), $fs->getDirectories($this->getPrefixed(''), true));
    }

    /**
     *
     */
    public function testApiGetDirectories_ReturnsContents_WhenFlagIsNotRecursive()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertSame($this->getPathData('', 'dir', false), $fs->getDirectories($this->getPrefixed(''), false));
    }

    /**
     *
     */
    public function testApiGetDirectories_ReturnsContents_WhenFilepatternIsSet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $filePattern = '#^DIR_#si';

        $test->assertSame($this->getPathData('', 'dir', true, $filePattern), $fs->getDirectories($this->getPrefixed(''), true, $filePattern));
    }

    /**
     *
     */
    public function testApiGetDirectories_ReturnsContents_WhenMultipleFilepatternsAreSet()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $filePatterns = [ '#^DIR_#si', '#_A$#si' ];

        $test->assertSame($this->getPathData('', 'dir', true, $filePatterns), $fs->getDirectories($this->getPrefixed(''), true, $filePatterns));
    }
}
