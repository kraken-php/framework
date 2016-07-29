<?php

namespace Kraken\_Module\Filesystem\_Partial\Filesystem;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TModule;
use Kraken\Throwable\Exception\Runtime\Io\IoWriteException;

trait FsApiCopyPartial
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
    public function testApiCopy_CopiesNode_WhenNodeIsFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $pa = $this->getPrefixed('FILE_A');
        $pnull = $this->getPrefixed('FILE_NULL');

        $test->assertTrue($fs->exists($pa));
        $test->assertFalse($fs->exists($pnull));

        $fs->copy($pa, $pnull);

        $test->assertTrue($fs->exists($pnull));
        $test->assertEquals($fs->read($pa), $fs->read($pnull));
    }

    /**
     *
     */
    public function testApiCopy_ThrowsException_WhenNodeIsDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $pa = $this->getPrefixed('DIR_A');
        $pnull = $this->getPrefixed('DIR_NULL');

        $test->setExpectedException(IoWriteException::class);
        $test->assertTrue($fs->exists($pa));
        $test->assertFalse($fs->exists($pnull));

        $fs->copy($pa, $pnull);
    }

    /**
     *
     */
    public function testApiCopy_CopiesNode_WhenNodeIsFileAndDestinationIsFile()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $pa = $this->getPrefixed('FILE_A');
        $pb = $this->getPrefixed('FILE_B');

        $test->setExpectedException(IoWriteException::class);
        $test->assertTrue($fs->exists($pa));
        $test->assertTrue($fs->exists($pb));

        $fs->copy($pa, $pb);
    }

    /**
     *
     */
    public function testApiCopy_ThrowsException_WhenNodeIsFileAndDestinationIsDirectory()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $pa = $this->getPrefixed('FILE_A');
        $pb = $this->getPrefixed('DIR_B');

        $test->setExpectedException(IoWriteException::class);
        $test->assertTrue($fs->exists($pa));
        $test->assertTrue($fs->exists($pb));

        $fs->copy($pa, $pb);
    }

    /**
     *
     */
    public function testApiCopy_ThrowsException_WhenSourceDoesNotExist()
    {
        $test = $this->getTest();
        $fs = $this->createFilesystem();

        $pa = $this->getPrefixed('FILE_NULL');
        $pb = $this->getPrefixed('NULL');

        $test->setExpectedException(IoWriteException::class);
        $test->assertFalse($fs->exists($pa));
        $test->assertFalse($fs->exists($pb));

        $fs->copy($pa, $pb);
    }
}
