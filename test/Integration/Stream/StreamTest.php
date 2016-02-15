<?php

namespace Kraken\Test\Integration\Stream;

use Kraken\Stream\Stream;
use Kraken\Test\Integration\TestCase;

class StreamTest extends TestCase
{
    public function testStreamWritesAndReadsDataCorrectly()
    {
        $local = $this->basePath();
        $writer = new Stream(fopen("file://$local/temp", 'w+'));
        $reader = new Stream(fopen("file://$local/temp", 'r+'));

        $data = "qwertyuiop\n";
        $writer->write($data);
        $this->assertEquals($data, $reader->read());

        unlink("$local/temp");
    }
}
