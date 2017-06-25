<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\Filesystem;
use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Dazzle\Throwable\Exception\Runtime\WriteException;

trait FsApiSetVisibilityPartial
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
    public function testApiSetVisibility_SetsPublic_WhenFileDoesExist_WhenNodeIsPublic()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertEquals(Filesystem::VISIBILITY_PUBLIC, $fs->getVisibility($this->getPrefixed('FILE_A')));
        $fs->setVisibility($this->getPrefixed('FILE_A'), Filesystem::VISIBILITY_PUBLIC);
        $test->assertEquals(Filesystem::VISIBILITY_PUBLIC, $fs->getVisibility($this->getPrefixed('FILE_A')));
    }

    /**
     *
     */
    public function testApiSetVisibility_SetsPublic_WhenFileDoesExist_WhenNodeIsPrivate()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertEquals(Filesystem::VISIBILITY_PRIVATE, $fs->getVisibility($this->getPrefixed('FILE_D')));
        $fs->setVisibility($this->getPrefixed('FILE_D'), Filesystem::VISIBILITY_PUBLIC);
        $test->assertEquals(Filesystem::VISIBILITY_PUBLIC, $fs->getVisibility($this->getPrefixed('FILE_D')));
    }

    /**
     *
     */
    public function testApiSetVisibility_SetsPrivate_WhenFileDoesExist_WhenNodeIsPublic()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertEquals(Filesystem::VISIBILITY_PUBLIC, $fs->getVisibility($this->getPrefixed('FILE_A')));
        $fs->setVisibility($this->getPrefixed('FILE_A'), Filesystem::VISIBILITY_PRIVATE);
        $test->assertEquals(Filesystem::VISIBILITY_PRIVATE, $fs->getVisibility($this->getPrefixed('FILE_A')));
    }

    /**
     *
     */
    public function testApiSetVisibility_SetsPrivate_WhenFileDoesExist_WhenNodeIsPrivate()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertEquals(Filesystem::VISIBILITY_PRIVATE, $fs->getVisibility($this->getPrefixed('FILE_D')));
        $fs->setVisibility($this->getPrefixed('FILE_D'), Filesystem::VISIBILITY_PRIVATE);
        $test->assertEquals(Filesystem::VISIBILITY_PRIVATE, $fs->getVisibility($this->getPrefixed('FILE_D')));
    }

    /**
     *
     */
    public function testApiSetVisibility_ThrowsException_WhenFileDoesExist_WhenVisibilityFlagIsNotValid()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->setExpectedException(WriteException::class);

        $fs->setVisibility($this->getPrefixed('FILE_D'), 'other');
    }

    /**
     *
     */
    public function testApiSetVisibility_SetsPublic_WhenDirectoryDoesExist_WhenNodeIsPublic()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertEquals(Filesystem::VISIBILITY_PUBLIC, $fs->getVisibility($this->getPrefixed('DIR_A')));
        $fs->setVisibility($this->getPrefixed('DIR_A'), Filesystem::VISIBILITY_PUBLIC);
        $test->assertEquals(Filesystem::VISIBILITY_PUBLIC, $fs->getVisibility($this->getPrefixed('DIR_A')));
    }

    /**
     *
     */
    public function testApiSetVisibility_SetsPublic_WhenDirectoryDoesExist_WhenNodeIsPrivate()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertEquals(Filesystem::VISIBILITY_PRIVATE, $fs->getVisibility($this->getPrefixed('DIR_D')));
        $fs->setVisibility($this->getPrefixed('DIR_D'), Filesystem::VISIBILITY_PUBLIC);
        $test->assertEquals(Filesystem::VISIBILITY_PUBLIC, $fs->getVisibility($this->getPrefixed('DIR_D')));
    }

    /**
     *
     */
    public function testApiSetVisibility_SetsPrivate_WhenDirectoryDoesExist_WhenNodeIsPublic()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertEquals(Filesystem::VISIBILITY_PUBLIC, $fs->getVisibility($this->getPrefixed('DIR_A')));
        $fs->setVisibility($this->getPrefixed('DIR_A'), Filesystem::VISIBILITY_PRIVATE);
        $test->assertEquals(Filesystem::VISIBILITY_PRIVATE, $fs->getVisibility($this->getPrefixed('DIR_A')));
    }

    /**
     *
     */
    public function testApiSetVisibility_SetsPrivate_WhenDirectoryDoesExist_WhenNodeIsPrivate()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->assertEquals(Filesystem::VISIBILITY_PRIVATE, $fs->getVisibility($this->getPrefixed('DIR_D')));
        $fs->setVisibility($this->getPrefixed('DIR_D'), Filesystem::VISIBILITY_PRIVATE);
        $test->assertEquals(Filesystem::VISIBILITY_PRIVATE, $fs->getVisibility($this->getPrefixed('DIR_D')));
    }

    /**
     *
     */
    public function testApiSetVisibility_ThrowsException_WhenDirectoryDoesExist_WhenVisibilityFlagIsNotValid()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->setExpectedException(WriteException::class);

        $fs->setVisibility($this->getPrefixed('FILE_D'), 'other');
    }

    /**
     *
     */
    public function testApiSetVisibility_ThrowsException_WhenPathDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $test->setExpectedException(WriteException::class);

        $fs->setVisibility($this->getPrefixed('FILE_NULL'), Filesystem::VISIBILITY_PUBLIC);
    }
}
