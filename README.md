This package was original was build by Tom Schlick, but I wanted a more out of the box 
experience with convention over configuration for the user 

You can find that here : 
https://github.com/tomschlick/request-migrations

# Laravel API Migrations

[![Latest Version on Packagist](https://img.shields.io/packagist/v/lukepolo/laravel-api-migrations.svg?style=flat-square)](https://packagist.org/packages/lukepolo/laravel-api-migrations)
[![Build Status](https://img.shields.io/travis/lukepolo/laravel-api-migrations/master.svg?style=flat-square)](https://travis-ci.org/lukepolo/laravel-api-migrations)
[![StyleCI](https://styleci.io/repos/102003593/shield)](https://styleci.io/repos/102003593)

This package is based on the [API versioning scheme used at Stripe](https://stripe.com/blog/api-versioning). Users pass a version header and you automatically migrate the request & response data to match the current version of your code.

## Installation

You can install the package via composer:

### Installation via Composer

```bash
composer require lukepolo/laravel-api-migrations
```
### Service Provider & Facade

This package supports Laravel 5.5 autoloading so the service provider and facade will be loaded automatically. 

If you are using an earlier version of Laravel or have autoloading disabled you need to add the service provider and facade to `config/app.php`.

```php
'providers' => [
    \LukePOLO\LaravelApiMigrations\ServiceProvider::class,
]
```

```php
'aliases' => [
    'LaravelApiMigrations' => '\LukePOLO\LaravelApiMigrations\Facades\LaravelApiMigrations::class,
]
```

### Middleware

Add the middleware to your Http Kernel `app/Http/Kernel.php`.

You have a couple of choices where to put this, recommenced under the api middleware!

```php
protected $middlewareGroups = [
    'api' => [
        \LukePOLO\LaravelApiMigrations\LaravelApiMigrationsMiddleware::class,
    ];
]
```

### Configuration

Run the following Artisan command to publish the package configuration to `config/request-migrations.php`.

```bash
php artisan vendor:publish --provider="LukePOLO\LaravelApiMigrations\ServiceProvider"
```

## Usage

### Creating a Migration

You can generate a new request migration using the Artisan CLI.

```shell
php artisan make:api-migration ExampleMigration

```
The command will generate a request migration and publish it to `App/Http/ApiMigrations/V*/Release_YYYY_MM_DD/*`.

### Caching Migrations

Once you move to prod you should cache the results

```shell
php artisan cache:api-migrations
```

### Pinning Versions to Users

You can auto pin versions to your users on their first hit to your api by enabling in the config. You must also
make the column `api_version` fillable in your `User` model!

### Override the Versions

```php
use LukePolo\LaravelApiMigrations\Facades\LaravelApiMigrations;

// set both response & request versions
LaravelApiMigrations::setVersion('2017-01-01')

// set the request version
LaravelApiMigrations::setRequestVersion('2017-01-01')

// set the response version
LaravelApiMigrations::setResponseVersion('2017-01-01')

```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email luke@lukepolo.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.