<?php

declare(strict_types=1);

namespace AiSdk\Replicate\Support;

use AiSdk\Generate;
use AiSdk\Exceptions\AiSdkException;
use AiSdk\Utils\Http\HttpRunner;
use AiSdk\Replicate\ReplicateOptions;

/**
 * @internal
 */
final class ReplicatePredictionClient
{
    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $replicateOpts
     * @return array<string, mixed>
     */
    public static function predictAndResolve(
        string $modelId,
        array $input,
        ReplicateOptions $options,
        array $replicateOpts = [],
    ): array {
        $pollIntervalMs = (int) ($replicateOpts['pollIntervalMs'] ?? 2000);
        $pollTimeoutMs = (int) ($replicateOpts['pollTimeoutMs'] ?? 300_000);
        $maxWait = $replicateOpts['maxWaitTimeInSeconds'] ?? null;

        foreach ($replicateOpts as $k => $v) {
            if (in_array((string) $k, ['pollIntervalMs', 'pollTimeoutMs', 'maxWaitTimeInSeconds'], true)) {
                continue;
            }
            if (! array_key_exists((string) $k, $input)) {
                $input[(string) $k] = $v;
            }
        }

        $parts = explode(':', $modelId, 2);
        $slug = $parts[0];
        $version = $parts[1] ?? null;

        $body = ['input' => $input];
        if ($version !== null) {
            $body['version'] = $version;
        }

        $path = $version !== null
            ? '/predictions'
            : '/models/' . rawurlencode($slug) . '/predictions';

        $url = rtrim($options->baseUrl, '/') . $path;

        $prefer = $maxWait !== null && is_numeric($maxWait)
            ? 'wait=' . $maxWait
            : 'wait';

        $headers = array_merge($options->authHeaders(), [
            'Prefer' => $prefer,
        ]);

        $runner = HttpRunner::fromSdk($options->sdk ?? Generate::sdk());
        $prediction = $runner->postJson($url, $body, $headers, ReplicateOptions::PROVIDER_NAME);

        return self::ensureComplete(
            $prediction,
            $options,
            $runner,
            $pollIntervalMs,
            $pollTimeoutMs,
        );
    }

    /**
     * @param  array<string, mixed>  $prediction
     * @return array<string, mixed>
     */
    private static function ensureComplete(
        array $prediction,
        ReplicateOptions $options,
        HttpRunner $runner,
        int $pollIntervalMs,
        int $pollTimeoutMs,
    ): array {
        $start = (int) (microtime(true) * 1000);

        $status = (string) ($prediction['status'] ?? '');
        while (in_array($status, ['starting', 'processing'], true)) {
            if ((int) (microtime(true) * 1000) - $start > $pollTimeoutMs) {
                throw new AiSdkException('Replicate prediction timed out.', ['provider' => ReplicateOptions::PROVIDER_NAME]);
            }
            usleep(max(1, $pollIntervalMs) * 1000);

            $getUrl = (string) ($prediction['urls']['get'] ?? '');
            if ($getUrl === '') {
                throw new AiSdkException('Replicate prediction missing poll URL.', ['provider' => ReplicateOptions::PROVIDER_NAME]);
            }

            $prediction = $runner->getJson($getUrl, $options->authHeaders(), ReplicateOptions::PROVIDER_NAME);
            $status = (string) ($prediction['status'] ?? '');
        }

        if ($status === 'failed') {
            throw new AiSdkException(
                'Replicate prediction failed: ' . ((string) ($prediction['error'] ?? 'unknown')),
                ['provider' => ReplicateOptions::PROVIDER_NAME],
            );
        }
        if ($status === 'canceled') {
            throw new AiSdkException('Replicate prediction was canceled.', ['provider' => ReplicateOptions::PROVIDER_NAME]);
        }

        return $prediction;
    }
}
