<?php

namespace Kraken\_Unit\Filesystem;

use Kraken\_Unit\Filesystem\_Mock\FilesystemMock;
use Kraken\Filesystem\Filesystem;
use Kraken\Filesystem\FilesystemInterface;
use Kraken\Filesystem\FilesystemManager;
use Kraken\Filesystem\FilesystemManagerInterface;
use Kraken\Test\TUnit;
use Kraken\Throwable\Exception\Runtime\Io\IoReadException;
use Kraken\Throwable\Exception\Runtime\Io\IoWriteException;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;

class FilesystemManagerTest extends TUnit
{
    /**
     * @dataProvider filesystemManagerProvider
     */
    public function testApiMountFilesystems_MountsFs(FilesystemManagerInterface $fs)
    {
        $prefix1 = 'prefix1';
        $prefix2 = 'prefix2';

        $this->assertFalse($fs->existsFilesystem($prefix1));
        $this->assertFalse($fs->existsFilesystem($prefix2));


        $fs->mountFilesystems([
            'prefix1' => $this->createFilesystemMock(),
            'prefix2' => $this->createFilesystemMock()
        ]);

        $this->assertTrue($fs->existsFilesystem($prefix1));
        $this->assertTrue($fs->existsFilesystem($prefix2));
    }

    /**
     * @dataProvider filesystemManagerProvider
     */
    public function testApiExistsFilesystem_ReturnsTrue_WhenFsExists(FilesystemManagerInterface $fs)
    {
        $this->assertTrue($fs->existsFilesystem('fs'));
    }

    /**
     * @dataProvider filesystemManagerProvider
     */
    public function testApiExistsFilesystem_ReturnsFalse_WhenFsDoesNotExist(FilesystemManagerInterface $fs)
    {
        $this->assertFalse($fs->existsFilesystem('not_fs'));
    }

    /**
     * @dataProvider filesystemManagerProvider
     */
    public function testApiMountFilesystem_MountsFs(FilesystemManagerInterface $fs)
    {
        $prefix = 'prefix';

        $this->assertFalse($fs->existsFilesystem($prefix));
        $fs->mountFilesystem($prefix, $this->createFilesystemMock());
        $this->assertTrue($fs->existsFilesystem($prefix));
    }

    /**
     * @dataProvider filesystemManagerProvider
     */
    public function testApiUnmountFilesystem_UnmountsFilesystem_WhenFsExists(FilesystemManagerInterface $fs)
    {
        $prefix = 'fs';

        $this->assertTrue($fs->existsFilesystem($prefix));
        $fs->unmountFilesystem($prefix);
        $this->assertFalse($fs->existsFilesystem($prefix));
    }

    /**
     * @dataProvider filesystemManagerProvider
     */
    public function testApiUnmountFilesystem_DoesNothing_WhenFsDoesNotExist(FilesystemManagerInterface $fs)
    {
        $prefix = 'not_fs';

        $this->assertFalse($fs->existsFilesystem($prefix));
        $fs->unmountFilesystem($prefix);
        $this->assertFalse($fs->existsFilesystem($prefix));
    }

    /**
     * @dataProvider filesystemManagerProvider
     */
    public function testApiGetFilesystem_ReturnsFilesystem_WhenFsExists(FilesystemManagerInterface $fs)
    {
        $prefix = 'prefix';
        $mock = $this->createFilesystemMock();

        $fs->mountFilesystem($prefix, $mock);
        $this->assertSame($mock, $fs->getFilesystem($prefix));
    }

    /**
     * @dataProvider filesystemManagerProvider
     */
    public function testApiGetFilesystem_ReturnsNull_WhenFsDoesNotExist(FilesystemManagerInterface $fs)
    {
        $prefix = 'prefix';

        $this->assertSame(null, $fs->getFilesystem($prefix));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiExists_PassesCallToProperFilesystem_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $path = 'path';

        $this->expect($p1, 'exists', [ $path ]);
        $man->exists('fs1://' . $path);
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiExists_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->exists('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiMove_ThrowsException_WhenSourceFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoWriteException::class);

        $man->move('not_fs://path', 'fs2://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiMove_ThrowsException_WhenDestFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoWriteException::class);

        $man->move('fs1://path', 'not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiIsFile_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $expected = new StdClass;
        $this->expect($p1, 'isFile', [ 'path' ])->willReturn($expected);
        $this->prevent($p2, 'isFile');
        $this->assertSame($expected, $man->isFile('fs1://path'));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiIsFile_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->isFile('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiIsDir_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $expected = new StdClass;
        $this->expect($p1, 'isDir', [ 'path' ])->willReturn($expected);
        $this->prevent($p2, 'isDir');
        $this->assertSame($expected, $man->isDir('fs1://path'));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiIsDir_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->isDir('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetContents_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $expected = new StdClass;
        $recursive = true;
        $filterPattern = function() {};

        $this->expect($p1, 'getContents', [ 'path', $recursive, $filterPattern ])->willReturn($expected);
        $this->prevent($p2, 'getContents');

        $this->assertSame($expected, $man->getContents('fs1://path', $recursive, $filterPattern));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetContents_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->getContents('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetFiles_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $expected = new StdClass;
        $recursive = true;
        $filterPattern = function() {};

        $this->expect($p1, 'getFiles', [ 'path', $recursive, $filterPattern ])->willReturn($expected);
        $this->prevent($p2, 'getFiles');

        $this->assertSame($expected, $man->getFiles('fs1://path', $recursive, $filterPattern));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetFiles_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->getFiles('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetDirectories_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $expected = new StdClass;
        $recursive = true;
        $filterPattern = function() {};

        $this->expect($p1, 'getDirectories', [ 'path', $recursive, $filterPattern ])->willReturn($expected);
        $this->prevent($p2, 'getDirectories');

        $this->assertSame($expected, $man->getDirectories('fs1://path', $recursive, $filterPattern));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetDirectories_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->getDirectories('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetVisibility_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $expected = new StdClass;

        $this->expect($p1, 'getVisibility', [ 'path' ])->willReturn($expected);
        $this->prevent($p2, 'getVisibility');

        $this->assertSame($expected, $man->getVisibility('fs1://path'));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetVisibility_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->getVisibility('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiIsPublic_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $expected = new StdClass;

        $this->expect($p1, 'isPublic', [ 'path' ])->willReturn($expected);
        $this->prevent($p2, 'isPublic');

        $this->assertSame($expected, $man->isPublic('fs1://path'));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiIsPublic_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->isPublic('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiIsPrivate_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $expected = new StdClass;

        $this->expect($p1, 'isPrivate', [ 'path' ])->willReturn($expected);
        $this->prevent($p2, 'isPrivate');

        $this->assertSame($expected, $man->isPrivate('fs1://path'));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiIsPrivate_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->isPrivate('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiSetVisibility_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $visibility = 'visibility';

        $this->expect($p1, 'setVisibility', [ 'path', $visibility ]);
        $this->prevent($p2, 'setVisibility');

        $man->setVisibility('fs1://path', $visibility);
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiSetVisibility_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $visibility = 'visibility';

        $this->setExpectedException(IoWriteException::class);

        $man->setVisibility('not_fs://path', $visibility);
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiSetPublic_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->expect($p1, 'setPublic', [ 'path' ]);
        $this->prevent($p2, 'setPublic');

        $man->setPublic('fs1://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiSetPublic_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoWriteException::class);

        $man->setPublic('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiSetPrivate_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->expect($p1, 'setPrivate', [ 'path' ]);
        $this->prevent($p2, 'setPrivate');

        $man->setPrivate('fs1://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiSetPrivate_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoWriteException::class);

        $man->setPrivate('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiCreate_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $contents = '';

        $this->expect($p1, 'create', [ 'path', $contents ]);
        $this->prevent($p2, 'create');

        $man->create('fs1://path', $contents);
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiCreate_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $contents = '';
        $this->setExpectedException(IoWriteException::class);

        $man->create('not_fs://path', $contents);
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiCreateFile_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $contents = '';
        $visibility = 'public';

        $this->expect($p1, 'createFile', [ 'path', $contents, $visibility ]);
        $this->prevent($p2, 'createFile');

        $man->createFile('fs1://path', $contents, $visibility);
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiCreateFile_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoWriteException::class);

        $man->createFile('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiWrite_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $contents = '';

        $this->expect($p1, 'write', [ 'path', $contents ]);
        $this->prevent($p2, 'write');

        $man->write('fs1://path', $contents);
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiWrite_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoWriteException::class);

        $contents = '';

        $man->write('not_fs://path', $contents);
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiAppend_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $contents = 'contents';

        $this->expect($p1, 'append', [ 'path', $contents ]);
        $this->prevent($p2, 'append');

        $man->append('fs1://path', $contents);
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiAppend_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoWriteException::class);

        $contents = 'contents';

        $man->append('not_fs://path', $contents);
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiPrepend_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $contents = 'contents';

        $this->expect($p1, 'prepend', [ 'path', $contents ]);
        $this->prevent($p2, 'prepend');

        $man->prepend('fs1://path', $contents);
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiPrepend_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoWriteException::class);

        $contents = 'contents';

        $man->prepend('not_fs://path', $contents);
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiRead_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $read = 'read';

        $this->expect($p1, 'read', [ 'path' ])->willReturn($read);
        $this->prevent($p2, 'read');

        $this->assertEquals($read, $man->read('fs1://path'));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiRead_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->read('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiReq_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $req = 'req';

        $this->expect($p1, 'req', [ 'path' ])->willReturn($req);
        $this->prevent($p2, 'req');

        $this->assertEquals($req, $man->req('fs1://path'));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiReq_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->req('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiRemoveFile_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->expect($p1, 'removeFile', [ 'path' ]);
        $this->prevent($p2, 'removeFile');

        $man->removeFile('fs1://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiRemoveFile_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoWriteException::class);

        $man->removeFile('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiEraseFile_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->expect($p1, 'eraseFile', [ 'path' ]);
        $this->prevent($p2, 'eraseFile');

        $man->eraseFile('fs1://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiEraseFile_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoWriteException::class);

        $man->eraseFile('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetSize_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $size = 255;

        $this->expect($p1, 'getSize', [ 'path' ])->willReturn($size);
        $this->prevent($p2, 'getSize');

        $this->assertEquals($size, $man->getSize('fs1://path'));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetSize_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->getSize('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetType_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $type = 'type';

        $this->expect($p1, 'getType', [ 'path' ])->willReturn($type);
        $this->prevent($p2, 'getType');

        $this->assertEquals($type, $man->getType('fs1://path'));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetType_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->getType('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetMimetype_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $mimetype = 'mimetype';

        $this->expect($p1, 'getMimetype', [ 'path' ])->willReturn($mimetype);
        $this->prevent($p2, 'getMimetype');

        $this->assertEquals($mimetype, $man->getMimetype('fs1://path'));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetMimetype_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->getMimetype('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetTimestamp_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $timestamp = time();

        $this->expect($p1, 'getTimestamp', [ 'path' ])->willReturn($timestamp);
        $this->prevent($p2, 'getTimestamp');

        $this->assertEquals($timestamp, $man->getTimestamp('fs1://path'));
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiGetTimestamp_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoReadException::class);

        $man->getTimestamp('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiCreateDir_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $visibility = 'public';

        $this->expect($p1, 'createDir', [ 'path', $visibility ]);
        $this->prevent($p2, 'createDir');

        $man->createDir('fs1://path', $visibility);
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiCreateDir_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoWriteException::class);

        $man->createDir('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiRemoveDir_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->expect($p1, 'removeDir', [ 'path' ]);
        $this->prevent($p2, 'removeDir');

        $man->removeDir('fs1://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiRemoveDir_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoWriteException::class);

        $man->removeDir('not_fs://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiEraseDir_PassesCallToProperFs_WhenFsExists(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->expect($p1, 'eraseDir', [ 'path' ]);
        $this->prevent($p2, 'eraseDir');

        $man->eraseDir('fs1://path');
    }

    /**
     * @dataProvider observableFilesystemManagerProvider
     */
    public function testApiEraseDir_ThrowsException_WhenFsDoesNotExist(FilesystemManagerInterface $man, ObjectProphecy $p1, ObjectProphecy $p2)
    {
        $this->setExpectedException(IoWriteException::class);

        $man->eraseDir('not_fs://path');
    }

    /**
     * @return mixed[]
     */
    public function filesystemManagerProvider()
    {
        $fs = $this->createFilesystemMock();

        return [
            [ $this->createFilesystemManager([ 'fs' => $fs ]) ]
        ];
    }

    /**
     * @return mixed[]
     */
    public function observableFilesystemManagerProvider()
    {
        $prophecy1 = $this->prophesize(Filesystem::class);
        $prophecy2 = $this->prophesize(Filesystem::class);
        $fs1 = $prophecy1->reveal();
        $fs2 = $prophecy2->reveal();

        return [
            [ $this->createFilesystemManager([ 'fs1' => $fs1, 'fs2' => $fs2 ]), $prophecy1, $prophecy2 ]
        ];
    }

    /**
     * @param ObjectProphecy $object
     * @param string $method
     * @param mixed[] $args
     * @param int $times
     * @return MethodProphecy
     */
    public function expect($object, $method, $args = null, $times = 1)
    {
        $args = $args === null ? [ Argument::any() ] : $args;
        $mock = call_user_func_array([ $object, $method ], $args);
        return $mock->shouldBeCalledTimes($times);
    }

    /**
     * @param ObjectProphecy $object
     * @param string $method
     * @param mixed[] $args
     * @return MethodProphecy
     */
    public function prevent($object, $method, $args = null)
    {
        return $this->expect($object, $method, $args, 0);
    }

    /**
     * @return FilesystemInterface
     */
    public function createFilesystemMock()
    {
        return $this->getMock(FilesystemMock::class);
    }

    /**
     * @param FilesystemInterface[] $fs
     * @return FilesystemManagerInterface
     */
    public function createFilesystemManager($fs = [])
    {
        return new FilesystemManager($fs);
    }
}
