<?php

namespace Kraken\_Unit\Filesystem;

use Kraken\_Unit\Filesystem\_Mock\FilesystemMock;
use Kraken\Filesystem\FilesystemInterface;
use Kraken\Test\TUnit;
use Kraken\Throwable\Exception\Runtime\Io\IoReadException;
use Kraken\Throwable\Exception\Runtime\Io\IoWriteException;
use League\Flysystem\Filesystem;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\MethodProphecy;
use Exception;

class FilesystemTest extends TUnit
{
    /**
     * @var ObjectProphecy
     */
    private $prophecy;

    /**
     * @var FilesystemInterface
     */
    private $fs;

    /**
     *
     */
    public function setUp()
    {
        $this->prophecy = $this->prophesize(Filesystem::class);
        $this->fs = $this->createFilesystem($this->prophecy->reveal());

        parent::setUp();
    }

    /**
     *
     */
    public function testApiExists_ReturnsTrue_WhenLeagueHasReturnsTrue()
    {
        $this->expect('has', [ 'path' ])->willReturn(true);
        $this->assertTrue($this->fs->exists('path'));
    }

    /**
     *
     */
    public function testApiExists_ReturnsFalse_WhenLeagueHasReturnsFalse()
    {
        $this->expect('has', [ 'path' ])->willReturn(false);
        $this->assertFalse($this->fs->exists('path'));
    }

    /**
     *
     */
    public function testApiExists_ThrowsException_WhenLeagueHasThrowsException()
    {
        $this->setExpectedException(IoReadException::class);

        $this->expect('has', [ 'path' ])->willThrow(new Exception());
        $this->fs->exists('path');
    }

    /**
     *
     */
    public function testApiMove_ReturnsNull_WhenLeagueMoveReturnsTrue()
    {
        $before = 'a';
        $after  = 'b';

        $this->expect('rename', [ $before, $after ])->willReturn(true);
        $this->fs->move($before, $after);
    }

    /**
     *
     */
    public function testApiMove_ThrowsException_WhenLeagueMoveReturnsFalse()
    {
        $this->setExpectedException(IoWriteException::class);

        $before = 'a';
        $after  = 'b';

        $this->expect('rename', [ $before, $after ])->willReturn(false);
        $this->fs->move($before, $after);
    }

    /**
     *
     */
    public function testApiMove_ThrowsException_WhenLeagueMoveThrowsException()
    {
        $this->setExpectedException(IoWriteException::class);

        $before = 'a';
        $after  = 'b';

        $this->expect('rename', [ $before, $after ])->willThrow(new Exception());
        $this->fs->move($before, $after);
    }

    /**
     *
     */
    public function testApiIsFile_ReturnsTrue_WhenLeagueReturnsMetadataForFile()
    {
        $path = 'path';

        $this->expect('getMetadata', [ $path ])->willReturn([ 'type' => 'file' ]);
        $this->assertTrue($this->fs->isFile($path));
    }

    /**
     *
     */
    public function testApiIsFile_ReturnsFalse_WhenLeagueDoesNotReturnMetadataForFile()
    {
        $path = 'path';

        $this->expect('getMetadata', [ $path ])->willReturn([ 'type' => 'other' ]);
        $this->assertFalse($this->fs->isFile($path));
    }

    /**
     *
     */
    public function testApiIsFile_ThrowsException_WhenLeagueThrowsException()
    {
        $this->setExpectedException(IoReadException::class);

        $path = 'path';

        $this->expect('getMetadata', [ $path ])->willThrow(new Exception());
        $this->fs->isFile($path);
    }

    /**
     *
     */
    public function testApiIsDir_ReturnsTrue_WhenLeagueReturnsMetadataForDir()
    {
        $path = 'path';

        $this->expect('getMetadata', [ $path ])->willReturn([ 'type' => 'dir' ]);
        $this->assertTrue($this->fs->isDir($path));
    }

    /**
     *
     */
    public function testApiIsDir_ReturnsFalse_WhenLeagueDoesNotReturnMetadataForDir()
    {
        $path = 'path';

        $this->expect('getMetadata', [ $path ])->willReturn([ 'type' => 'other' ]);
        $this->assertFalse($this->fs->isDir($path));
    }

    /**
     *
     */
    public function testApiIsDir_ThrowsException_WhenLeagueThrowsException()
    {
        $this->setExpectedException(IoReadException::class);

        $path = 'path';

        $this->expect('getMetadata', [ $path ])->willThrow(new Exception());
        $this->fs->isDir($path);
    }

    /**
     *
     */
    public function testApiGetVisibility_ReturnsSameValue_AsLeagueGetVisibility()
    {
        $path = 'path';
        $str = 'XYZ';

        $this->expect('getVisibility', [ $path ])->willReturn($str);
        $this->assertEquals($str, $this->fs->getVisibility($path));
    }

    /**
     *
     */
    public function testApiGetVisibility_ThrowsException_WhenLeagueThrowsException()
    {
        $this->setExpectedException(IoReadException::class);

        $path = 'path';

        $this->expect('getVisibility', [ $path ])->willThrow(new Exception());
        $this->fs->getVisibility($path);
    }

    /**
     *
     */
    public function testApiIsPublic_ReturnsTrue_WhenVisibilityIsPublic()
    {
        $path = 'path';

        $this->expect('getVisibility', [ $path ])->willReturn('public');
        $this->assertTrue($this->fs->isPublic($path));
    }

    /**
     *
     */
    public function testApiIsPublic_ReturnsFalse_WhenVisibilityIsNotPublic()
    {
        $path = 'path';

        $this->expect('getVisibility', [ $path ])->willReturn('other');
        $this->assertFalse($this->fs->isPublic($path));
    }

    /**
     *
     */
    public function testApiIsPublic_RethrowsException()
    {
        $this->setExpectedException(IoReadException::class);

        $path = 'path';

        $this->expect('getVisibility', [ $path ])->willThrow(new Exception());
        $this->fs->isPublic($path);
    }

    /**
     *
     */
    public function testApiIsPrivate_ReturnsTrue_WhenVisibilityIsPrivate()
    {
        $path = 'path';

        $this->expect('getVisibility', [ $path ])->willReturn('private');
        $this->assertTrue($this->fs->isPrivate($path));
    }

    /**
     *
     */
    public function testApiIsPrivate_ReturnsFalse_WhenVisibilityIsNotPrivate()
    {
        $path = 'path';

        $this->expect('getVisibility', [ $path ])->willReturn('other');
        $this->assertFalse($this->fs->isPrivate($path));
    }

    /**
     *
     */
    public function testApiIsPrivate_RethrowsException()
    {
        $this->setExpectedException(IoReadException::class);

        $path = 'path';

        $this->expect('getVisibility', [ $path ])->willThrow(new Exception());
        $this->fs->isPrivate($path);
    }

    /**
     *
     */
    public function testApiSetVisibility_SetsVisibility_WhenSettingIsPossible()
    {
        $path = 'path';
        $visibility = 'visibility';

        $this->expect('setVisibility', [ $path, $visibility ])->willReturn(true);
        $this->fs->setVisibility($path, $visibility);
    }

    /**
     *
     */
    public function testApiSetVisibility_ThrowsException_WhenSettingIsNotPossible()
    {
        $this->setExpectedException(IoWriteException::class);

        $path = 'path';
        $visibility = 'visibility';

        $this->expect('setVisibility', [ $path, $visibility ])->willReturn(false);
        $this->fs->setVisibility($path, $visibility);
    }

    /**
     *
     */
    public function testApiSetVisibility_ThrowsException_WhenModelThrows()
    {
        $this->setExpectedException(IoWriteException::class);

        $path = 'path';
        $visibility = 'visibility';


        $this->expect('setVisibility', [ $path, $visibility ])->willThrow(new Exception());
        $this->fs->setVisibility($path, $visibility);
    }

    /**
     *
     */
    public function testApiSetPublic_SetsPublic_WhenSettingIsPossible()
    {
        $path = 'path';

        $this->expect('setVisibility', [ $path, 'public' ])->willReturn(true);
        $this->fs->setPublic($path);
    }

    /**
     *
     */
    public function testApiSetPublic_ThrowsException_WhenSettingIsNotPossible()
    {
        $this->setExpectedException(IoWriteException::class);

        $path = 'path';

        $this->expect('setVisibility', [ $path, 'public' ])->willReturn(false);
        $this->fs->setPublic($path);
    }

    /**
     *
     */
    public function testApiSetPublic_ThrowsException_WhenModelThrows()
    {
        $this->setExpectedException(IoWriteException::class);

        $path = 'path';

        $this->expect('setVisibility', [ $path, 'public' ])->willThrow(new Exception());
        $this->fs->setPublic($path);
    }

    /**
     *
     */
    public function testApiSetPrivate_SetsPrivate_WhenSettingIsPossible()
    {
        $path = 'path';

        $this->expect('setVisibility', [ $path, 'private' ])->willReturn(true);
        $this->fs->setPrivate($path);
    }

    /**
     *
     */
    public function testApiSetPrivate_ThrowsException_WhenSettingIsNotPossible()
    {
        $this->setExpectedException(IoWriteException::class);

        $path = 'path';

        $this->expect('setVisibility', [ $path, 'private' ])->willReturn(false);
        $this->fs->setPrivate($path);
    }

    /**
     *
     */
    public function testApiSetPrivate_ThrowsException_WhenModelThrows()
    {
        $this->setExpectedException(IoWriteException::class);

        $path = 'path';

        $this->expect('setVisibility', [ $path, 'private' ])->willThrow(new Exception());
        $this->fs->setPrivate($path);
    }

    /**
     *
     */
    public function testApiCreate_CallsWriteOnLeague_WithConfig()
    {
        $path = 'path';
        $contents = 'contents';
        $visibility = 'visibility';

        $this->expect('write', [ $path, $contents, $this->prepareConfig($visibility) ]);
        $this->fs->create($path, $contents, $visibility);
    }

    /**
     *
     */
    public function testApiCreate_ThrowsException_WhenLegueWriteThrowsException()
    {
        $path = 'path';
        $contents = 'contents';
        $visibility = 'visibility';
        $expected = new Exception();
        $ex = null;

        $this->expect('write', [ $path, $contents, $this->prepareConfig($visibility) ])->willThrow($expected);

        try
        {
            $this->fs->create($path, $contents, $visibility);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoWriteException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiWrite_CallsPutOnLeague_WithConfig()
    {
        $path = 'path';
        $contents = 'contents';
        $visibility = 'visibility';

        $this->expect('put', [ $path, $contents, $this->prepareConfig($visibility) ]);
        $this->fs->write($path, $contents, $visibility);
    }

    /**
     *
     */
    public function testApiWrite_ThrowsException_WhenLeguePutThrowsException()
    {
        $path = 'path';
        $contents = 'contents';
        $visibility = 'visibility';
        $expected = new Exception();
        $ex = null;

        $this->expect('put', [ $path, $contents, $this->prepareConfig($visibility) ])->willThrow($expected);

        try
        {
            $this->fs->write($path, $contents, $visibility);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoWriteException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiAppend_CallsUpdateOnLeague_WithExistingContents()
    {
        $path = 'path';
        $write = 'write';
        $read = 'read';

        $this->expect('read', [ $path ])->willReturn($read);
        $this->expect('update', [ $path, $read . $write ]);

        $this->fs->append($path, $write);
    }

    /**
     *
     */
    public function testApiAppend_ThrowsException_WhenReadThrowsException()
    {
        $path = 'path';
        $write = 'write';
        $read = 'read';
        $expected = new Exception();
        $ex = null;

        $this->expect('read', [ $path ])->willThrow($expected);
        $this->prevent('update', [ $path, $read . $write ]);

        try
        {
            $this->fs->append($path, $write);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoWriteException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiAppend_ThrowsException_WhenUpdateThrowsException()
    {
        $path = 'path';
        $write = 'write';
        $read = 'read';
        $expected = new Exception();
        $ex = null;

        $this->expect('read', [ $path ])->willReturn($read);
        $this->expect('update', [ $path, $read . $write ])->willThrow($expected);

        try
        {
            $this->fs->append($path, $write);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoWriteException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiPrepend_CallsUpdateOnLeague_WithExistingContents()
    {
        $path = 'path';
        $write = 'write';
        $read = 'read';

        $this->expect('read', [ $path ])->willReturn($read);
        $this->expect('update', [ $path, $write . $read ]);

        $this->fs->prepend($path, $write);
    }

    /**
     *
     */
    public function testApiPrepend_ThrowsException_WhenReadThrowsException()
    {
        $path = 'path';
        $write = 'write';
        $read = 'read';
        $expected = new Exception();
        $ex = null;

        $this->expect('read', [ $path ])->willThrow($expected);
        $this->prevent('update', [ $path, $write . $read ]);

        try
        {
            $this->fs->prepend($path, $write);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoWriteException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiPrepend_ThrowsException_WhenUpdateThrowsException()
    {
        $path = 'path';
        $write = 'write';
        $read = 'read';
        $expected = new Exception();
        $ex = null;

        $this->expect('read', [ $path ])->willReturn($read);
        $this->expect('update', [ $path, $write . $read ])->willThrow($expected);

        try
        {
            $this->fs->prepend($path, $write);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoWriteException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiRead_ReturnsReadString_WhenReadWasSuccessful()
    {
        $path = 'path';
        $str = 'XYZ';

        $this->expect('read', [ $path ])->willReturn($str);
        $this->assertEquals($str, $this->fs->read($path));
    }

    /**
     *
     */
    public function testApiRead_ThrowsException_WhenReadWasNotSuccessful()
    {
        $path = 'path';
        $ex = null;

        $this->expect('read', [ $path ])->willReturn(false);

        try
        {
            $this->fs->read($path);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoReadException::class, $ex);
        $this->assertSame(null, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiRead_ThrowsException_WhenLeagueReadThrowsException()
    {
        $path = 'path';
        $ex = null;

        $this->expect('read', [ $path ])->willThrow($expected = new Exception());

        try
        {
            $this->fs->read($path);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoReadException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiReq_UsesDataStreamToIncludePhpFilesFromExternalSources()
    {
        $path = 'path';
        $str = 'XYZ';

        $this->expect('read', [ $path ])->willReturn($str);

        $this->assertEquals(
            "data://text/plain;base64," . base64_encode($str),
            $this->fs->req($path)
        );
    }

    /**
     *
     */
    public function testApiReq_ThrowsException_WhenLeagueReadThrowsException()
    {
        $path = 'path';
        $ex = null;

        $this->expect('read', [ $path ])->willThrow($expected = new Exception());

        try
        {
            $this->fs->req($path);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoReadException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiCopy_CallsCopyOnModel()
    {
        $from = 'from';
        $to = 'to';

        $this->expect('copy', [ $from, $to ]);
        $this->fs->copy($from, $to);
    }

    /**
     *
     */
    public function testApiCopy_ThrowsException_WhenModelThrowsException()
    {
        $from = 'from';
        $to = 'to';
        $expected = new Exception();
        $ex = null;

        $this->expect('copy', [ $from, $to ])->willThrow($expected);

        try
        {
            $this->fs->copy($from, $to);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoWriteException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiRemove_CallsDeleteOnModel()
    {
        $path = 'path';

        $this->expect('delete', [ $path ]);
        $this->fs->remove($path);
    }

    /**
     *
     */
    public function testApiRemove_ThrowsException_WhenDeleteOnModelThrowsException()
    {
        $path = 'path';
        $expected = new Exception();
        $ex = null;

        $this->expect('delete', [ $path ])->willThrow($expected);

        try
        {
            $this->fs->remove($path);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoWriteException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiErase_CallsUpdateOnModel()
    {
        $path = 'path';

        $this->expect('update', [ $path, '' ]);
        $this->fs->erase($path);
    }

    /**
     *
     */
    public function testApiErase_ThrowsException_WhenUpdateOnModelThrowsException()
    {
        $path = 'path';
        $expected = new Exception();
        $ex = null;

        $this->expect('update', [ $path, '' ])->willThrow($expected);

        try
        {
            $this->fs->erase($path);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoWriteException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiGetSize_CallsGetSizeOnModel()
    {
        $path = 'path';
        $size = 255;

        $this->expect('getSize', [ $path ])->willReturn($size);
        $this->assertEquals($size, $this->fs->getSize($path));
    }

    /**
     *
     */
    public function testApiGetSize_ThrowsException_WhenGetSizeOnModelThrowsException()
    {
        $path = 'path';
        $expected = new Exception();
        $ex = null;

        $this->expect('getSize', [ $path ])->willThrow($expected);

        try
        {
            $this->fs->getSize($path);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoReadException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiGetType_ReturnsType_UsingGetMetadataOnModel()
    {
        $path = 'path';
        $type = 'some_type';

        $this->expect('getMetadata', [ $path ])->willReturn([ 'type' => $type ]);
        $this->assertEquals($type, $this->fs->getType($path));
    }

    /**
     *
     */
    public function testApiGetType_ThrowsException_WhenGetMetadataOnModelThrowsException()
    {
        $path = 'path';
        $expected = new Exception();
        $ex = null;

        $this->expect('getMetadata', [ $path ])->willThrow($expected);

        try
        {
            $this->fs->getType($path);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoReadException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiGetMimetype_ReturnsMimetype_UsingGetMetadataOnModel()
    {
        $path = 'path';
        $array = [ 'key1' => 'val1', 'key2' => 'val2' ];

        $this->expect('getMimetype', [ $path ])->willReturn($array);
        $this->assertEquals($array, $this->fs->getMimetype($path));
    }

    /**
     *
     */
    public function testApiGetMimetype_ThrowsException_WhenGetMetadataOnModelThrowsException()
    {
        $path = 'path';
        $expected = new Exception();
        $ex = null;

        $this->expect('getMimetype', [ $path ])->willThrow($expected);

        try
        {
            $this->fs->getMimetype($path);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoReadException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiGetTimestamp_ReturnsMimetype_UsingGetMetadataOnModel()
    {
        $path = 'path';
        $timestamp = time();

        $this->expect('getTimestamp', [ $path ])->willReturn($timestamp);
        $this->assertEquals($timestamp, $this->fs->getTimestamp($path));
    }

    /**
     *
     */
    public function testApiGetTimestamp_ThrowsException_WhenGetMetadataOnModelThrowsException()
    {
        $path = 'path';
        $expected = new Exception();
        $ex = null;

        $this->expect('getTimestamp', [ $path ])->willThrow($expected);

        try
        {
            $this->fs->getTimestamp($path);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoReadException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiCreateDir_CallsCreateDirOnModel()
    {
        $path = 'path';
        $visibility = 'visibility';

        $this->expect('createDir', [ $path, $this->prepareConfig($visibility) ]);
        $this->fs->createDir($path, $visibility);
    }

    /**
     *
     */
    public function testApiCreateDir_ThrowsException_WhenCreateDirOnModelThrowsException()
    {
        $path = 'path';
        $visibility = 'visibility';
        $expected = new Exception();
        $ex = null;

        $this->expect('createDir', [ $path, $this->prepareConfig($visibility) ])->willThrow($expected);

        try
        {
            $this->fs->createDir($path, $visibility);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoWriteException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     *
     */
    public function testApiRemoveDir_CallsDeleteDirOnModel()
    {
        $path = 'path';

        $this->expect('deleteDir', [ $path ]);
        $this->fs->removeDir($path);
    }

    /**
     *
     */
    public function testApiRemoveDir_ThrowsException_WhenDeleteDirOnModelThrowsException()
    {
        $path = 'path';
        $expected = new Exception();
        $ex = null;

        $this->expect('deleteDir', [ $path ])->willThrow($expected);

        try
        {
            $this->fs->removeDir($path);
        }
        catch (Exception $ex)
        {}

        $this->assertInstanceOf(IoWriteException::class, $ex);
        $this->assertSame($expected, $ex->getPrevious());
    }

    /**
     * @return MethodProphecy
     */
    public function expect($method, $args = [], $times = 1)
    {
        $mock = call_user_func_array([ $this->prophecy, $method ], $args);
        return $mock->shouldBeCalledTimes($times);
    }

    /**
     * @return MethodProphecy
     */
    public function prevent($method, $args = [])
    {
        return $this->expect($method, $args, 0);
    }

    /**
     * @return FilesystemInterface
     */
    public function createFilesystem($internal)
    {
        return $this->setProtectedProperty(new FilesystemMock, 'fs', $internal);
    }

    /**
     * @param $visibility
     * @return string[]
     */
    private function prepareConfig($visibility)
    {
        if ($visibility === 'default')
        {
            return [];
        }

        return [
            'visibility' => $visibility
        ];
    }
}
