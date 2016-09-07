<?php

namespace Kraken\_Integration\Boot;

use Composer\Autoload\ClassLoader;
use Kraken\Framework\Console\Server\Boot\ServerBoot;
use Kraken\Test\TModule;

class ConsoleServerTest extends TModule
{
    /**
     *
     */
    public function testCaseServer_DoesNotThrowException_WhenBooted()
    {
        if (ini_get('allow_url_include') !== '1')
        {
            return;
        }

        global $loader;
        $loader = $this->getMock(ClassLoader::class, [], [], '', false);

        $dataPath = realpath(__DIR__ . '/../../../') . '/data';
        $console  = (new ServerBoot)
            ->boot(
                $dataPath
            );

        $console->destroy();

        unset($console);
    }
}
