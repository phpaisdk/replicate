# aisdk/replicate

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
