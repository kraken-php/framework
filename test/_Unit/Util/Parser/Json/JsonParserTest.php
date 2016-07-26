<?php

namespace Kraken\_Unit\Util\Parser\Json;

use Kraken\Test\TUnit;
use Kraken\Util\Parser\Json\JsonParser;
use Kraken\Util\Parser\ParserInterface;
use StdClass;

class JsonParserTest extends TUnit
{
    /**
     *
     */
    public function testApiIsSupported_ReturnsTrue_ForExistingConstants()
    {
        $parser = $this->createParser();

        $consts = $parser::getSupported();

        foreach ($consts as $const)
        {
            $this->assertTrue($parser->isSupported($const));
        }
    }

    /**
     *
     */
    public function testApiGetSupported_ReturnsSupportedConstants()
    {
        $parser = $this->createParser();

        $this->assertEquals(
            [
                'DECODE_DEFAULT' => $parser::DECODE_DEFAULT,
                'DECODE_ARRAY'   => $parser::DECODE_ARRAY,
                'DECODE_OBJECT'  => $parser::DECODE_OBJECT
            ],
            $parser::getSupported()
        );
    }

    /**
     * @dataProvider parsersProvider
     */
    public function testApiEncode_EncodesObjectToJson_ForDefaultDecoder(ParserInterface $parser)
    {
        $this->assertEquals($this->getString(), $parser->encode($this->getObject()));
    }

    /**
     * @dataProvider parsersProvider
     */
    public function testApiEncode_EncodesArrayToJson_ForDefaultDecoder(ParserInterface $parser)
    {
        $this->assertEquals($this->getString(), $parser->encode($this->getArray()));
    }

    /**
     *
     */
    public function testApiDecode_DecodesJsonToArray_ForDecodeObjectFlag()
    {
        $parser = $this->createParser(JsonParser::DECODE_OBJECT);

        $this->assertEquals($this->getObject(), $parser->decode($this->getString()));
    }

    /**
     * @return ParserInterface[]
     */
    public function parsersProvider()
    {
        return [
            [ $this->createParser() ],
            [ $this->createParser(JsonParser::DECODE_ARRAY) ],
            [ $this->createParser(JsonParser::DECODE_OBJECT) ]
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

    /**
     * @return string
     */
    protected function getString()
    {
        return '{"a":"A","b":{"a":[],"b":4,"c":null},"c":[]}';
    }

    /**
     * @param int $flags
     * @return JsonParser
     */
    protected function createParser($flags = JsonParser::DECODE_DEFAULT)
    {
        return new JsonParser($flags);
    }
}
