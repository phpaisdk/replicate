<?php

declare(strict_types=1);

namespace AiSdk\Replicate\Models;

use AiSdk\Contracts\BaseModel;
use AiSdk\Contracts\ImageModelInterface;
use AiSdk\Replicate\ReplicateOptions;
use AiSdk\Replicate\Support\ReplicatePredictionClient;
use AiSdk\Requests\ImageRequest;
use AiSdk\Responses\ImageResponse;
use AiSdk\Results\ImageData;
use AiSdk\Support\Usage;

final class ReplicateImageModel extends BaseModel implements ImageModelInterface
{
    public function __construct(private readonly string $modelId, private readonly ReplicateOptions $options) {}
    public function provider(): string
    {
        return ReplicateOptions::PROVIDER_NAME;
    }
    public function modelId(): string
    {
        return $this->modelId;
    }
    public function generate(ImageRequest $r): ImageResponse
    {
        $p = ReplicatePredictionClient::predictAndResolve($this->modelId, array_replace(['prompt' => $r->prompt], $r->size ? ['size' => $r->size] : [], $r->aspectRatio ? ['aspect_ratio' => $r->aspectRatio] : [], $r->seed !== null ? ['seed' => $r->seed] : []), $this->options, $r->providerOptionsFor($this->provider()));
        $urls = is_array($p['output'] ?? null) ? $p['output'] : [$p['output'] ?? null];
        $images = [];
        foreach ($urls as $url) {
            if (is_string($url) && $url !== '') {
                $images[] = new ImageData(url: $url);
            }
        }return new ImageResponse($images, Usage::empty(), $p, [$this->provider() => ['predictionId' => $p['id'] ?? null]]);
    }
}
