<?php

/**
 * Jsonl - A simple library for encoding and decoding JSON Lines (jsonl) format.
 *
 * This library provides methods to encode and decode JSON Lines (jsonl) format,
 * which is a convenient way to store and process large datasets in a line-oriented
 * manner.
*/

namespace Indykoning\Jsonl;

use Generator;
use InvalidArgumentException;
use Traversable;

/**
 * The Jsonl class provides methods to encode and decode JSON Lines (jsonl) format.
 */
class Jsonl
{
    /**
     * Parses a JSON Lines (jsonl) string or iterable
     * into a Generator array of associative arrays.
     *
     * @param Traversable|array|string $jsonl       The JSON Lines string or iterable to parse.
     * @param bool|null                $associative Wether to return associative arrays or objects.
     * @param int                      $depth       The maximum depth to decode.
     *
     * @return Generator
     */
    public static function decode(
        Traversable|array|string $jsonl,
        ?bool $associative = null,
        int $depth = 512
    ): Generator {
        if (is_string($jsonl)) {
            $jsonl = explode("\n", $jsonl);
        }

        foreach ($jsonl as $line) {
            if (empty($line) || !is_string($line)) {
                continue;
            }

            yield json_decode(
                trim($line),
                $associative,
                $depth,
                JSON_THROW_ON_ERROR
            );
        }
    }

    /**
     * Parses a JSON Lines (jsonl) stream/resource into a
     * Generator array of associative arrays.
     *
     * @param resource  $resource    The resource to read from.
     * @param bool|null $associative Wether to return associative arrays or objects.
     * @param int       $depth       The maximum depth to decode.
     *
     * @return Generator
     */
    public static function decodeFromResource(
        $resource,
        ?bool $associative = null,
        int $depth = 512
    ): Generator {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('decodeFromResource expects a resource "' . gettype($resource) . '" was passed');
        }

        while (($line = fgets($resource)) !== false) {
            foreach (static::decode($line, $associative, $depth) as $item) {
                yield $item;
            }
        }
    }

    /**
     * Encodes an iterable of associative arrays into a JSON Lines (jsonl) generator.
     *
     * @param Traversable|array $lines The List of object/arrays to encode.
     *
     * @return Generator
     */
    public static function encode(Traversable|array $lines): Generator
    {
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }

            yield json_encode($line) . "\n";
        }
    }

    /**
     * Encodes and writes an iterable of associative arrays into a resource/stream.
     *
     * @param resource          $resource The resource to write to.
     * @param Traversable|array $lines    The List of object/arrays to encode.
     *
     * @return void
     */
    public static function encodeToResource(
        $resource,
        Traversable|array $lines
    ): void {
        if (!is_resource($resource)) {
            throw new InvalidArgumentException('encodeToResource expects a resource "' . gettype($resource) . '" was passed');
        }

        foreach (self::encode($lines) as $line) {
            if (!fwrite($resource, $line)) {
                throw new \RuntimeException('Failed to write to resource');
            }
        }
    }
}
