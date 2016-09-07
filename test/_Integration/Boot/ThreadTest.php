<?php

namespace Kraken\_Integration\Boot;

use Composer\Autoload\ClassLoader;
use Kraken\_Integration\Boot\_Mock\MockedThreadContainer;
use Kraken\Framework\Runtime\Boot\ThreadBoot;
use Kraken\Test\TModule;

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

        $dataPath = realpath(__DIR__ . '/../../../') . '/data';
        $thread   = (new ThreadBoot)
            ->controller(
                MockedThreadContainer::class
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
