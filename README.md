# Laravel Rivers

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lsv-eu/laravel-rivers.svg?style=flat-square)](https://packagist.org/packages/lsv-eu/laravel-rivers)
[![Total Downloads](https://img.shields.io/packagist/dt/lsv-eu/laravel-rivers.svg?style=flat-square)](https://packagist.org/packages/lsv-eu/laravel-rivers)
![GitHub Actions](https://github.com/lsv-eu/rivers/actions/workflows/main.yml/badge.svg)
![GitHub Actions](https://github.com/lsv-eu/rivers/actions/workflows/lint.yml/badge.svg)

Laravel Rivers is a system to create and run user-definable, mutable rivers in Laravel. Rivers are similar to pipelines, automations, journeys, workflows, etc. For this reason, we have adopted [river-based language](https://github.com/lsv-eu/laravel-rivers/blob/main/docs/terminology.md) to, hopefully, decrease confusion.

## Documentation

- [Terminology](https://github.com/lsv-eu/laravel-rivers/blob/main/docs/terminology.md)
- (In progress)

## Installation

You can install the package via composer:

```bash
composer require lsv-eu/rivers
```

(Optional) Install the config
```shell
php artisan vendor:public --tag=rivers-config
```

## Usage

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security-related issues, please email leo.lutz@lsv.eu instead of using the issue tracker.

## Credits

-   [Leo Lutz](https://github.com/skeemer)
-   [Paul Riddick](https://github.com/paulriddickeu)
-   [All Contributors](https://github.com/lsv-eu/laravel-rivers/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
