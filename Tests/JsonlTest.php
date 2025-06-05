<?php

use Indykoning\Jsonl\Jsonl;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class JsonlTest extends TestCase
{
    public function testJsonlEncode()
    {
        $data = [['key' => 'value'], ['key2' => 'value2']];
        $expected = ['{"key":"value"}' . "\n", '{"key2":"value2"}' . "\n"];

        $this->assertEquals($expected, iterator_to_array(Jsonl::encode($data)));
    }

    public function testJsonlDecode()
    {
        $jsonlString = '{"key":"value"}' . "\n" . '{"key2":"value2"}' . "\n";
        $expected = [['key' => 'value'], ['key2' => 'value2']];
        $this->assertEquals($expected, iterator_to_array(Jsonl::decode($jsonlString, true)));
    }

    public function testJsonlEncodeToFile()
    {
        $data = [['key' => 'value'], ['key2' => 'value2']];
        $expected = '{"key":"value"}' . "\n" . '{"key2":"value2"}' . "\n";

        $file = fopen(__DIR__ . '/data/test_encode_to_file.jsonl', 'w');
        Jsonl::encodeToResource($file, $data);
        fclose($file);

        $this->assertEquals($expected, file_get_contents(__DIR__ . '/data/test_encode_to_file.jsonl'));

        unlink(__DIR__ . '/data/test_encode_to_file.jsonl');
    }

    public function testJsonlEncodeIncorrectResource()
    {
        $data = [['key' => 'value'], ['key2' => 'value2']];
        $this->expectException(InvalidArgumentException::class);
        Jsonl::encodeToResource('', $data);
    }

    public function testJsonlDecodeFromFile()
    {
        $expected = [['key' => 'value'], ['key2' => 'value2']];
        $file = fopen(__DIR__ . '/data/test_decode.jsonl', 'r');

        $this->assertEquals($expected, iterator_to_array(Jsonl::decodeFromResource($file, true)));
        fclose($file);
    }

    public function testJsonlDecodeIncorrectResource()
    {
        $this->expectException(InvalidArgumentException::class);
        iterator_to_array(Jsonl::decodeFromResource('', true));
    }
}