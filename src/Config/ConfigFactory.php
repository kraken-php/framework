<?php

namespace Kraken\Config;

use Kraken\Filesystem\FilesystemInterface;
use Kraken\Util\Factory\SimpleFactory;
use Kraken\Util\Parser\ParserInterface;

class ConfigFactory extends SimpleFactory implements ConfigFactoryInterface
{
    /**
     * @param FilesystemInterface $fs
     * @param string[] $masks
     * @param ParserInterface[] $parsers
     * @param bool $recursive
     */
    public function __construct(FilesystemInterface $fs, $masks = [], $parsers = [], $recursive = false)
    {
        parent::__construct();

        $factory = $this;
        $factory->bindParam('fs', $fs);
        $factory->bindParam('masks', $masks);
        $factory->bindParam('parsers', $parsers);
        $factory->bindParam('recursive', $recursive);

        $factory->define(function(callable $overwriteHandler = null) {
            $fs = $this->getParam('fs');
            $masks = $this->getParam('masks');
            $parsers = $this->getParam('parsers');
            $recursive = $this->getParam('recursive');

            $filters = $masks;
            $filters[] = '#(' . $this->getPatternForExt() . ')$#si';

            $files = $fs->getFiles('', $recursive, $filters);
            $data = [];
            $config = new Config($data, $overwriteHandler);

            foreach ($files as $file) {
                $ext = $file['extension'];
                $data = [];
                if ($ext === 'php') {
                    $data = $fs->req($file['path']);
                } else {
                    if (isset($parsers[$ext])) {
                        $contents = $fs->read($file['path']);
                        $data = $parsers[$ext]->decode($contents);
                    }
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
        $patterns = [
            '\.php'
        ];
        $parsers = $this->getParam('parsers');

        foreach (array_keys($parsers) as $ext)
        {
            $patterns[] = '\.' . $ext;
        }

        return implode("|", $patterns);
    }
}
