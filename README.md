# aisdk/replicate

<a href="https://github.com/phpaisdk/replicate/actions"><img alt="GitHub Workflow Status" src="https://img.shields.io/github/actions/workflow/status/phpaisdk/replicate/tests.yml?branch=main&label=Tests"></a>
<a href="https://packagist.org/packages/aisdk/replicate"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/aisdk/replicate"></a>
<a href="https://packagist.org/packages/aisdk/replicate"><img alt="Latest Version" src="https://img.shields.io/packagist/v/aisdk/replicate"></a>
<a href="https://packagist.org/packages/aisdk/replicate"><img alt="License" src="https://img.shields.io/packagist/l/aisdk/replicate"></a>
<a href="https://whyphp.dev"><img src="https://img.shields.io/badge/Why_PHP-in_2026-7A86E8?style=flat-square&labelColor=18181b" alt="Why PHP in 2026"></a>

------

[Replicate](https://replicate.com/) provider for the PHP AI SDK — image and video generation via the `/v1/predictions` API (`prefer: wait` + polling), consistent with `@ai-sdk/replicate`.

## Installation

```bash
composer require aisdk/replicate
```

## Configuration

| Variable               | Purpose                              |
| ---------------------- | ------------------------------------ |
| `REPLICATE_API_TOKEN`  | API token (required).               |
| `REPLICATE_BASE_URL`   | Defaults to `https://api.replicate.com/v1`. |

Model IDs use either `owner/name` or `version-hash:version` forms as in the upstream SDK.

## Usage

```php
use AiSdk\Generate;
use AiSdk\Replicate;

Generate::configure(Generate::factory()->make());

$result = Generate::image()
    ->model(Replicate::image('black-forest-labs/flux-schnell'))
    ->prompt('A red balloon')
    ->run();
```

Video jobs resolve to a `VideoJob` with a result URL in `VideoData`. Provider options under `replicate` support `maxWaitTimeInSeconds`, `pollIntervalMs`, and `pollTimeoutMs`, plus model-specific input fields.

## License

MIT.
