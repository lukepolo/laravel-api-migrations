# Laravel API Migrations
[![Latest Version on Packagist](https://img.shields.io/packagist/v/lukepolo/laravel-api-migrations.svg?style=flat-square)](https://packagist.org/packages/lukepolo/laravel-api-migrations)
[![Build Status](https://img.shields.io/travis/lukepolo/laravel-api-migrations/master.svg?style=flat-square)](https://travis-ci.org/lukepolo/laravel-api-migrations)
[![StyleCI](https://styleci.io/repos/102003593/shield)](https://styleci.io/repos/102003593)

This package is based on the [API versioning scheme used at Stripe](https://stripe.com/blog/api-versioning). Users pass a version header and you automatically migrate the request & response data to match the current version of your code.

## TLDR 
You can update your API without worrying of users applications breaking with API Migrations. 

You write these incrementing migrations to convert your request/responses go back in time to allow your users applications to work flawlessly.

### Features :
* User Version Pinning
* Major API Versioning Supported
* Convention Supplied with artisan commands

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

### Making HTTP Requests
By default when making a request to your API it will not run any migrations.

To use a different version of your api just attach a header :

```
    'Api-Version' : '2017-08-31'  
```

### Writing the API migrations

`migrateRequest` method : This is used to convert your request to be valid to your most current route

`migrateResponse` method : This is used to convert your response to what you should expect for that version

Example : -- link to gist --

### User Version Pinning
With version pinning you can automatically keep users to that API and allow them to upgrade to your latest version at their
convince.

Run the migration to enable version pinning. Then once your user hits your api for the first time it will set the most current version. 

!!! NOTE !!!
You must also make the column `api_version` fillable in your `User` model!

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


## Credits 
This package was original idea build by Tom Schlick, but modified heavily.
 
https://github.com/tomschlick/request-migrations