<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\Filesystem;
use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Dazzle\Throwable\Exception\Runtime\WriteException;

trait FsApiCreateFilePartial
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
    public function testApiCreateFile_ReplacesNode_WhenNodeIsFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $dest = $this->getPrefixed('FILE_A');
        $before = 'FILE_A_TEXT';
        $after  = 'FILE_A_NEW_TEXT';

        $test->assertTrue($fs->exists($dest));
        $test->assertSame($before, $fs->read($dest));
        $fs->createFile($dest, $after);

        $test->assertTrue($fs->exists($dest));
        $test->assertSame($after, $fs->read($dest));
    }

    /**
     *
     */
    public function testApiCreateFile_ThrowsException_WhenNodeIsDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $dest = $this->getPrefixed('DIR_A');
        $after  = 'FILE_A_NEW_TEXT';

        $test->setExpectedException(WriteException::class);
        $test->assertTrue($fs->exists($dest));
        $fs->createFile($dest, $after);
    }

    /**
     *
     */
    public function testApiCreateFile_CreatesFile_WhenNodeDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $dest = $this->getPrefixed('NULL');
        $text = 'FILE_A_NEW_TEXT';

        $test->assertFalse($fs->exists($dest));
        $fs->createFile($dest, $text);

        $test->assertTrue($fs->exists($dest));
        $test->assertSame($text, $fs->read($dest));
    }

    /**
     *
     */
    public function testApiCreateFile_CreatesPublicFile_WhenVisibilityFlagSetToPublic()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $dest = $this->getPrefixed('NULL');
        $text = 'FILE_A_NEW_TEXT';

        $test->assertFalse($fs->exists($dest));
        $fs->createFile($dest, $text, Filesystem::VISIBILITY_PUBLIC);

        $test->assertTrue($fs->exists($dest));
        $test->assertSame($text, $fs->read($dest));
        $test->assertTrue($fs->isPublic($dest));
    }

    /**
     *
     */
    public function testApiCreateFile_CreatesPrivateFile_WhenVisibilityFlagSetToPrivate()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();
        $dest = $this->getPrefixed('NULL');
        $text = 'FILE_A_NEW_TEXT';

        $test->assertFalse($fs->exists($dest));
        $fs->createFile($dest, $text, Filesystem::VISIBILITY_PRIVATE);

        $test->assertTrue($fs->exists($dest));
        $test->assertSame($text, $fs->read($dest));
        $test->assertTrue($fs->isPrivate($dest));
    }
}
