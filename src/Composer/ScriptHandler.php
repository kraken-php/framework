<?php

namespace Kraken\Composer;

use Composer\Script\CommandEvent;

final class ScriptHandler
{
    private static $options = [
        'kraken-config-file' => 'test'
    ];

    public static function buildBootstrap(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $configPath = $options['kraken-config-file'];

        var_dump(getcwd());
        var_dump($options);

//        $event->getIO()->write(sprintf('The %s (%s) specified in composer.json was not found in %s, can not %s.', 'kraken-config-file', $configPath, getcwd(), 'build bootstrap file'));
    }

    protected static function getOptions(CommandEvent $event)
    {
        return array_merge(self::$options, $event->getComposer()->getPackage()->getExtra());
    }
}
