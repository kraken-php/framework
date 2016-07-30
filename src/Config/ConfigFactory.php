<?php

namespace Kraken\Config;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Util\Factory\SimpleFactory;
use Kraken\Util\Parser\ParserInterface;

class ConfigFactory extends SimpleFactory implements ConfigFactoryInterface
{
    /**
     * @param FilesystemInterface $fs
     * @param ParserInterface[] $parsers
     */
    public function __construct(FilesystemInterface $fs, $parsers = [])
    {
        parent::__construct();

        $factory = $this;
        $factory->bindParam('fs', $fs);
        $factory->bindParam('parsers', $parsers);

        $factory->define(function(callable $overwriteHandler = null) {
            $fs = $this->getParam('fs');
            $parsers = $this->getParam('parsers');

            $files = $fs->getFiles('', true, '#(' . $this->getPatternForExt() . ')$#si');
            $data = [];
            $config = new Config($data, $overwriteHandler);

            foreach ($files as $file)
            {
                $ext = $file['extension'];
                $contents = $fs->read($file['path']);
                $data = [];

                if ($ext === 'php')
                {
                    $data = require "data://text/plain;base64," . base64_encode($contents);
                }
                else if (isset($parsers[$ext]))
                {
                    $data = $parsers[$ext]->decode($contents);
                }

                $config->merge($data);
            }

            return $config;
        });
    }

    /**
     * @return string
     */
    private function getPatternForExt()
    {
        $pattern = [
            '\.php'
        ];
        $parsers = $this->getParam('parsers');

        foreach (array_keys($parsers) as $ext)
        {
            $pattern[] = '\.' . $ext;
        }

        return implode("|", $pattern);
    }
}
