<?php

declare(strict_types=1);

namespace AiSdk\Replicate;

use AiSdk\Support\Sdk;
use AiSdk\Utils\Support\Env;
use AiSdk\Utils\Support\Url;

final class ReplicateOptions
{
    public const DEFAULT_BASE_URL = 'https://api.replicate.com/v1';

    public const PROVIDER_NAME = 'replicate';

    /**
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public readonly string $apiToken,
        public readonly string $baseUrl = self::DEFAULT_BASE_URL,
        public readonly array $headers = [],
        public readonly ?Sdk $sdk = null,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config = []): self
    {
        $token = Env::loadApiKey(
            isset($config['apiToken']) ? (string) $config['apiToken'] : null,
            'REPLICATE_API_TOKEN',
            self::PROVIDER_NAME,
        );

        $baseUrl = Url::withoutTrailingSlash(
            Env::loadOptionalSetting(isset($config['baseURL']) ? (string) $config['baseURL'] : null, 'REPLICATE_BASE_URL')
                ?? self::DEFAULT_BASE_URL,
        );

        /** @var array<string, string> $headers */
        $headers = isset($config['headers']) && is_array($config['headers']) ? $config['headers'] : [];

        $sdk = $config['sdk'] ?? null;

        return new self(
            apiToken: $token,
            baseUrl: $baseUrl,
            headers: $headers,
            sdk: $sdk instanceof Sdk ? $sdk : null,
        );
    }

    /**
     * @return array<string, string>
     */
    public function authHeaders(): array
    {
        return array_merge([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ], $this->headers);
    }
}
