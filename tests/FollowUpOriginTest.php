<?php

declare(strict_types=1);

use AiSdk\Replicate\ReplicateOptions;
use AiSdk\Replicate\Support\ReplicatePredictionClient;
use AiSdk\Exceptions\InvalidResponseException;
use AiSdk\Replicate\Tests\Fakes\SequenceHttpClient;
use AiSdk\Support\Sdk;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;

require_once __DIR__ . '/Fakes/SequenceHttpClient.php';

function followUp(string $url, string $baseUrl = 'https://api.example.com/v1'): array
{
    $client = new SequenceHttpClient([
        new Response(200, [], json_encode(['status' => 'processing', 'urls' => ['get' => $url, 'cancel' => 'https://evil.test/cancel']], JSON_THROW_ON_ERROR)),
        new Response(200, [], json_encode(['status' => 'succeeded', 'output' => ['https://media.example/image.png']], JSON_THROW_ON_ERROR)),
    ]);
    $factory = new Psr17Factory();
    $options = new ReplicateOptions(apiToken: 'secret', baseUrl: $baseUrl, sdk: new Sdk($client, $factory, $factory));
    $run = fn() => ReplicatePredictionClient::predictAndResolve('owner/model', ['prompt' => 'Hello'], $options, ['pollIntervalMs' => 0]);

    return [$run, $client];
}

it('permits follow-ups only on the configured origin', function (string $url) {
    [$run, $client] = followUp($url);
    $run();
    expect($client->requests)->toHaveCount(2);
    expect(end($client->requests)->getHeaderLine('Authorization'))->toBe('Bearer secret');
})->with([
    'https://api.example.com/result?token=signed',
    'https://API.EXAMPLE.COM:443/result',
]);

it('rejects untrusted follow-up origins before sending credentials', function (string $url) {
    [$run, $client] = followUp($url);
    try {
        $run();
        test()->fail('Expected origin rejection.');
    } catch (InvalidResponseException $error) {
        expect($error->getMessage())->toBe('Provider follow-up URL has an untrusted origin.');
        expect((string) $error)->not->toContain('signed-secret');
    }
    expect($client->requests)->toHaveCount(1);
})->with([
    'https://evil.test/result?token=signed-secret',
    'http://api.example.com/result',
    'https://api.example.com:444/result',
    'https://api.example.com@evil.test/result',
    'https://user:password@api.example.com/result',
    'https://api.example.com.evil.test/result',
    'https://api.example.com\\@evil.test/result',
    '/relative/result',
    '//api.example.com/result',
    'https://api.example.com:99999/result',
]);

it('honors an explicitly configured non-default port', function () {
    [$run, $client] = followUp('https://custom.example:8443/result', 'https://custom.example:8443/v1');
    $run();
    expect($client->requests)->toHaveCount(2);
});

it('revalidates the poll URL in every prediction response', function () {
    $client = new SequenceHttpClient([
        new Response(200, [], '{"status":"processing","urls":{"get":"https://api.example.com/first"}}'),
        new Response(200, [], '{"status":"processing","urls":{"get":"https://evil.test/second"}}'),
    ]);
    $factory = new Psr17Factory();
    $options = new ReplicateOptions(apiToken: 'secret', baseUrl: 'https://api.example.com/v1', sdk: new Sdk($client, $factory, $factory));
    expect(fn() => ReplicatePredictionClient::predictAndResolve('model', [], $options, ['pollIntervalMs' => 0]))->toThrow(InvalidResponseException::class);
    expect($client->requests)->toHaveCount(2);
});

it('does not echo signed URLs from prediction failure messages', function () {
    $client = new SequenceHttpClient([new Response(200, [], '{"status":"failed","error":"https://media.example/?signature=secret"}')]);
    $factory = new Psr17Factory();
    $options = new ReplicateOptions(apiToken: 'key', sdk: new Sdk($client, $factory, $factory));
    expect(fn() => ReplicatePredictionClient::predictAndResolve('model', [], $options))->toThrow(\AiSdk\Exceptions\AiSdkException::class, 'Replicate prediction failed.');
    expect($client->requests)->toHaveCount(1);
});
