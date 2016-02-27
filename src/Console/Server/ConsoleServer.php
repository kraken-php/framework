<?php

namespace Kraken\Console\Server;

use Kraken\Core\CoreInterface;
use Kraken\Runtime\Container\ProcessContainer;
use Kraken\Runtime\RuntimeInterface;

class ConsoleServer extends ProcessContainer
{
    /**
     * @param CoreInterface $core
     * @return array
     */
    protected function config(CoreInterface $core)
    {
        return [];
    }

    /**
     * @param CoreInterface $core
     * @return RuntimeInterface
     */
    protected function construct(CoreInterface $core)
    {
        echo "Server is up!\n";

        $this->onCreate(function() {
            $this->onCreateHandler();
        });

        return $this;
    }

    /**
     *
     */
    protected function onCreateHandler()
    {
//        $manager = $this->manager();
//        $manager
//            ->createThread('A', 'Common')
//            ->then(function() {
//                echo "It works!\n";
//            });
    }
}
