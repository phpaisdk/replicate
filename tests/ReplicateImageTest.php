<?php

declare(strict_types=1);

use AiSdk\Generate;
use AiSdk\Replicate;
use AiSdk\Replicate\Tests\Fakes\FakeHttpClient;
use AiSdk\Support\Sdk;

afterEach(function (): void {
    Generate::reset();
    Replicate::reset();
});

it('creates a native Replicate image prediction', function (): void {
    $client = new FakeHttpClient(200, json_encode(['id' => 'pred-1', 'status' => 'succeeded', 'output' => ['https://replicate.delivery/image.png']]));
    $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
    Generate::configure(new Sdk($client, $factory, $factory));
    Replicate::create(['apiToken' => 'r8_test']);
    $result = Generate::image('A sunset')->model(Replicate::model('black-forest-labs/flux-schnell'))->run();
    expect($result->images[0]->url)->toBe('https://replicate.delivery/image.png')
        ->and($client->sentBody()['input']['prompt'])->toBe('A sunset')
        ->and($client->lastRequest->getHeaderLine('Authorization'))->toBe('Bearer r8_test');
});
