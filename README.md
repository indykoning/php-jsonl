PHP JsonL
================

This package implements the [JSON Lines](http://jsonlines.org/) format allowing you to encode and decode jsonl values.
To take full advantage of the format we use [PHP's Generators](https://www.php.net/manual/en/language.generators.php)

#### Installation via Composer
``` bash
composer require indykoning/php-jsonl
```

#### Usage

##### Decoding

To decode jsonl you can use the `decode`:
```php
\Indykoning\Jsonl\Jsonl::decode($jsonl, $associative);
```

The jsonl variable can be a string of jsonl lines, an array of jsonl lines, or any Traversable.
So anything that can be iterated over and contains valid json can be passed.

If you want to decode the jsonl from a file, or decode it as you're downloading it you can use `decodeFromResource`
```php
\Indykoning\Jsonl\Jsonl::decodeFromResource($resource, $associative);
```

Some examples you can use for this are:
```php
$data = \Indykoning\Jsonl\Jsonl::decodeFromResource(fopen('/tmp/data.jsonl', 'r'), true);

$data = \Indykoning\Jsonl\Jsonl::decodeFromResource(
    \Illuminate\Support\Facades\Http::get('https://example.com/data.jsonl')->resource()
);
```

#### Encoding

Encoding can be done by calling `encode` with a Traversable or array with the objects inside
```php
\Indykoning\Jsonl\Jsonl::encode($array);

\Indykoning\Jsonl\Jsonl::encode([
    ['name' => 'Gilbert', 'session' => '2013', 'score' => 24, 'completed' => true],
    ['name' => 'Alexa', 'session' => '2013', 'score' => 29, 'completed' => true],
]);
```

Encoding also supports writing directly to a resource using `encodeToResource`:
```php
\Indykoning\Jsonl\Jsonl::encodeToResource($resource, $array);

\Indykoning\Jsonl\Jsonl::encodeToResource(
    fopen('/tmp/data.jsonl', 'w'), 
    [
        ['name' => 'Gilbert', 'session' => '2013', 'score' => 24, 'completed' => true],
        ['name' => 'Alexa', 'session' => '2013', 'score' => 29, 'completed' => true],
    ]
);
```

#### Example using both

In practice this means you could proxy a theoretically infititely long file in json without issue.
```php
function enrichData($data) 
{
    foreach($data as $object)
    {
        $object->enriched_data = EnrichmentModel::find($object->id)->toArray();

        yield $object;
    }
}

response()->streamDownload(
    function () {
        $data = \Indykoning\Jsonl\Jsonl::decodeFromResource(
            \Illuminate\Support\Facades\Http::get('https://example.com/data.jsonl')->resource()
        );

        $data = enrichData($data);

        foreach(\Indykoning\Jsonl\Jsonl::encode($data) as $chunk) {
            echo $chunk;
            ob_flush();
            flush();
        }
    },
    'data.jsonl',
    [
        'Content-Type' => 'application/jsonl',
        'X-Accel-Buffering' => 'no'
    ]
);
```

With this code we'll be dealing with every json line one by one, enriching the data and passing it on to the client.
When the client stops requesting data we will stop requesting and enriching data.

#### Running tests
``` bash
composer test
```

#### License
This library is licensed under the MIT license. Please see [LICENSE](LICENSE) for more details.