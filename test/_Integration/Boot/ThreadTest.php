<?php

namespace Kraken\_Integration\Boot;

use Kraken\_Integration\Boot\_Mock\RuntimeContainerMock;
use Kraken\Root\Runtime\Boot\ThreadBoot;
use Kraken\Test\TModule;
use Composer\Autoload\ClassLoader;

class ThreadTest extends TModule
{
    /**
     *
     */
    public function testCaseThread_DoesNotThrowException_WhenBooted()
    {
        if (ini_get('allow_url_include') !== '1')
        {
            return;
        }

        global $loader;
        $loader = $this->getMock(ClassLoader::class, [], [], '', false);

        $dataPath = realpath(__DIR__ . '/..') . '/_Data';
        $thread   = (new ThreadBoot)
            ->controller(
                RuntimeContainerMock::class
            )
            ->constructor([
                'undefined',
                'alias',
                'name'
            ])
            ->boot(
                $dataPath
            );

        $thread
            ->destroy();

        unset($thread);
    }
}
