<?php

declare(strict_types=1);

namespace AiSdk\Replicate;

use AiSdk\Contracts\BaseProvider;
use AiSdk\Contracts\ImageModelInterface;
use AiSdk\Contracts\ImageProviderInterface;
use AiSdk\Contracts\VideoModelInterface;
use AiSdk\Contracts\VideoProviderInterface;
use AiSdk\Replicate\Models\ReplicateImageModel;
use AiSdk\Replicate\Models\ReplicateVideoModel;

final class ReplicateProvider extends BaseProvider implements ImageProviderInterface, VideoProviderInterface
{
    public function __construct(public readonly ReplicateOptions $options) {}

    public function name(): string
    {
        return ReplicateOptions::PROVIDER_NAME;
    }

    protected function imageModel(string $modelId): ImageModelInterface
    {
        return new ReplicateImageModel($modelId, $this->options);
    }

    protected function videoModel(string $modelId): VideoModelInterface
    {
        return new ReplicateVideoModel($modelId, $this->options);
    }

}
