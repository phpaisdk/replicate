<?php

declare(strict_types=1);

namespace AiSdk;

use AiSdk\Contracts\Model;
use AiSdk\Replicate\ReplicateOptions;
use AiSdk\Replicate\ReplicateProvider;

/**
 * Replicate predictions API for image and video models.
 */
final class Replicate
{
    private static ?ReplicateProvider $default = null;

    /**
     * @param  array<string, mixed>  $config
     */
    public static function create(array $config = []): ReplicateProvider
    {
        $provider = new ReplicateProvider(ReplicateOptions::fromArray($config));
        self::$default = $provider;

        return $provider;
    }

    public static function default(): ReplicateProvider
    {
        return self::$default ??= self::create();
    }

    public static function reset(): void
    {
        self::$default = null;
    }

    public static function model(string $modelId): Model
    {
        return self::default()->model($modelId);
    }
}
