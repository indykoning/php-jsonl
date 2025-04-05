<?php

namespace Indykoning\Jsonl;

use Generator;
use Traversable;

/**
 * Jsonl class provides methods to encode and decode JSON Lines (jsonl) format.
 */
class Jsonl
{
    /**
     * Parses a JSON Lines (jsonl) string or iterable into a Generator array of associative arrays.
     */
    public static function decode(Traversable|array|string $jsonl, ?bool $associative = null, int $depth = 512): Generator
    {
        if (is_string($jsonl)) {
            $jsonl = explode("\n", $jsonl);
        }

        foreach ($jsonl as $line) {
            if (empty($line) || !is_string($line)) {
                continue;
            }

            yield json_decode(trim($line), $associative, $depth, JSON_THROW_ON_ERROR);
        }
    }

    /**
     * Parses a JSON Lines (jsonl) stream/resource into a Generator array of associative arrays.
     *
     * @param resource $resource
     */
    public static function decodeFromResource($resource, ?bool $associative = null, int $depth = 512): Generator
    {
        while (($line = fgets($resource)) !== false) {
            foreach (static::decode($line, $associative, $depth) as $item) {
                yield $item;
            }
        }
    }

    /**
     * Encodes an iterable of associative arrays into a JSON Lines (jsonl) generator.
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
     * @param resource $resource
     * @param Traversable|array $lines
     */
    public static function encodeToResource($resource, Traversable|array $lines)
    {
        foreach (self::encode($lines) as $line) {
            if (!fwrite($resource, $line)) {
                throw new \RuntimeException('Failed to write to resource');
            }
        }
    }
}
