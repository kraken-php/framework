<?php

namespace Kraken\_Integration\Boot;

use Kraken\Root\Console\Client\Boot\ClientBoot;
use Kraken\Test\TModule;

class ConsoleClientTest extends TModule
{
    /**
     *
     */
    public function testCaseClient_DoesNotThrowException_WhenBooted()
    {
        if (ini_get('allow_url_include') !== '1')
        {
            return;
        }

        $dataPath = realpath(__DIR__ . '/..') . '/_Data';
        $console  = (new ClientBoot)
            ->boot(
                $dataPath
            );

        $console->stop();

        unset($console);
    }
}
