<?php

namespace Kraken\Config;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Parser\ParserInterface;

class ConfigFactory
{
    /**
     * @var FilesystemInterface
     */
    protected $fs;

    /**
     * @var ParserInterface[]
     */
    protected $parsers;

    /**
     * @param FilesystemInterface $fs
     * @param ParserInterface[] $parsers
     */
    public function __construct(FilesystemInterface $fs, $parsers = [])
    {
        $this->fs = $fs;
        $this->parsers = $parsers;
    }

    /**
     * @return ConfigInterface
     */
    public function create()
    {
        $files = $this->fs->files('', true, '#(' . $this->patternExt() . ')$#si');
        $config = [];

        foreach ($files as $file)
        {
            $ext = $file['extension'];
            $contents = $this->fs->read($file['path']);

            if ($ext === 'php')
            {
                $data = require "data://text/plain;base64," . base64_encode($contents);
            }
            else
            {
                $data = $this->parsers[$ext]->decode($contents);
            }

            $config = array_merge($config, $data);
        }

        return new Config($config);
    }

    /**
     * @return string
     */
    private function patternExt()
    {
        $pattern = [
            '\.php'
        ];
        foreach (array_keys($this->parsers) as $ext)
        {
            $pattern[] = '\.' . $ext;
        }

        return implode("|", $pattern);
    }
}
