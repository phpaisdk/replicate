<?php

declare(strict_types=1);

namespace AiSdk\Replicate\Models;

use AiSdk\ContentSource;
use AiSdk\Contracts\BaseModel;
use AiSdk\Contracts\VideoModelInterface;
use AiSdk\Replicate\ReplicateOptions;
use AiSdk\Replicate\Support\ReplicatePredictionClient;
use AiSdk\Requests\VideoRequest;
use AiSdk\Responses\VideoJob;
use AiSdk\Responses\VideoJobStatus;
use AiSdk\Results\VideoData;
use AiSdk\Support\Usage;

final class ReplicateVideoModel extends BaseModel implements VideoModelInterface
{
    public function __construct(private readonly string $modelId, private readonly ReplicateOptions $options) {} public function provider(): string
    {
        return ReplicateOptions::PROVIDER_NAME;
    } public function modelId(): string
    {
        return $this->modelId;
    } public function generate(VideoRequest $r): VideoJob
    {
        $p = ReplicatePredictionClient::predictAndResolve($this->modelId, array_replace(['prompt' => $r->prompt], $r->image?->source() === ContentSource::Url ? ['image' => $r->image->url()] : [], $r->providerOptionsFor($this->provider())), $this->options);
        $o = $p['output'] ?? null;
        $u = is_array($o) ? ($o[0] ?? null) : $o;
        return new VideoJob((string) ($p['id'] ?? ''), $this->provider(), $this->modelId, VideoJobStatus::Succeeded, is_string($u) ? new VideoData(url: $u) : null, usage: Usage::empty(), rawResponse: $p);
    } public function poll(VideoJob $j): VideoJob
    {
        return $j;
    }
}
