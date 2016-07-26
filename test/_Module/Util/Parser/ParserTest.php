<?php

namespace Kraken\_Module\Util\Parser;

use Kraken\Test\TModule;
use Kraken\Util\Parser\Json\JsonParser;
use Kraken\Util\Parser\ParserInterface;
use StdClass;

class ParserTest extends TModule
{
    /**
     * @dataProvider arrayParsersProvider
     */
    public function testCaseParser_EncodesThenDecodesDataProperly_ForArrayParsers(ParserInterface $parser)
    {
        $data = $this->getArray();

        $this->assertSame($data, $parser->decode($parser->encode($data)));
    }

    /**
     * @dataProvider objectParsersProvider
     */
    public function testCaseParser_EncodesThenDecodesDataProperly_ForObjectParsers(ParserInterface $parser)
    {
        $data = $this->getObject();

        $this->assertEquals($data, $parser->decode($parser->encode($data)));
    }

    /**
     * @return ParserInterface[][]
     */
    public function objectParsersProvider()
    {
        return [
            [ new JsonParser(JsonParser::DECODE_OBJECT) ]
        ];
    }

    /**
     * @return ParserInterface[][]
     */
    public function arrayParsersProvider()
    {
        return [
            [ new JsonParser() ],
            [ new JsonParser(JsonParser::DECODE_ARRAY) ]
        ];
    }

    /**
     * @return StdClass
     */
    protected function getObject()
    {
        $std = new StdClass;

        $std->a = 'A';
        $std->b = new StdClass;
        $std->c = [];

        $std->b->a = [];
        $std->b->b = 4;
        $std->b->c = null;

        return $std;
    }

    /**
     * @return mixed[]
     */
    protected function getArray()
    {
        return [
            'a' => 'A',
            'b' => [
                'a' => [],
                'b' => 4,
                'c' => null
            ],
            'c' => []
        ];
    }
}
