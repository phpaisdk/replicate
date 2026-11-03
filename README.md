# PHP AI SDK — Replicate

Image generation with Replicate for the framework-agnostic [PHP AI SDK](https://phpaisdk.com). Also supports video generation.

## Installation

```bash
composer require aisdk/replicate
```

Set `REPLICATE_API_TOKEN` in your environment.

## Usage

```php
use AiSdk\Generate;
use AiSdk\Replicate;

$result = Generate::image('A mountain lake at sunrise, soft natural light.')
    ->model(Replicate::model('black-forest-labs/flux-2-pro'))
    ->run();

$result->output->save(__DIR__.'/image.png');
```

## Documentation

[Read the documentation](https://phpaisdk.com/docs/0.x/providers/replicate) for configuration, supported features, and more examples.

[Contributing](https://github.com/phpaisdk/.github/blob/main/CONTRIBUTING.md) · [Support](https://github.com/phpaisdk/.github/blob/main/SUPPORT.md)

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
