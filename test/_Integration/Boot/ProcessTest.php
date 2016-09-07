<?php

namespace Kraken\_Integration\Boot;

use Composer\Autoload\ClassLoader;
use Kraken\_Integration\Boot\_Mock\MockedProcessContainer;
use Kraken\Framework\Runtime\Boot\ProcessBoot;
use Kraken\Test\TModule;

class ProcessTest extends TModule
{
    /**
     *
     */
    public function testCaseProcess_DoesNotThrowException_WhenBooted()
    {
        if (ini_get('allow_url_include') !== '1')
        {
            return;
        }

        global $loader;
        $loader = $this->getMock(ClassLoader::class, [], [], '', false);

        $dataPath = realpath(__DIR__ . '/../../../') . '/data';
        $process  = (new ProcessBoot)
            ->controller(
                MockedProcessContainer::class
            )
            ->constructor([
                'undefined',
                'alias',
                'name'
            ])
            ->boot(
                $dataPath
            );

        $process
            ->destroy();

        unset($process);
    }
}
