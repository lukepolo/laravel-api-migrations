# Laravel API Migrations
[![Latest Version on Packagist](https://img.shields.io/packagist/v/lukepolo/laravel-api-migrations.svg?style=flat-square)](https://packagist.org/packages/lukepolo/laravel-api-migrations)
[![Build Status](https://img.shields.io/travis/lukepolo/laravel-api-migrations/master.svg?style=flat-square)](https://travis-ci.org/lukepolo/laravel-api-migrations)
[![StyleCI](https://styleci.io/repos/102003593/shield)](https://styleci.io/repos/102003593)

This package is based on the [API versioning scheme used at Stripe](https://stripe.com/blog/api-versioning). Users pass a version header and you automatically migrate the request & response data to match the current version of your code.

## TLDR 
You can update your API without worrying of users applications breaking with API Migrations. 

You write these incrementing migrations to convert your request/responses go back in time to allow your users applications to work flawlessly.

## Features
* User Version Pinning
* Major API Versioning Supported
* Convention Supplied with artisan commands

## How to use in day to day development 
You should create releases (including your current release) when releasing your API.
This allows the system to know how to migrate a request/response to and older version of the API.

For example :

**Current** *Release V1 - 2017-08-31* expects the response :

```php
    [
        'firstname' => 'Dwight',
        'lastname'  => 'Schrute',
        'title'     => 'Assistant to the Regional Manager'
    ]
```

Release *V1 - 2017-08-01* expects the response :

```php
    [
        'firstname' => 'Dwight',
        'lastname'  => 'Schrute',
        'title'     => 'Assistant to the Regional Manager',
        'secret_title' => 'Assistant Regional Manager',
    ]
```

When your users are using the older API they expect to see that secret title.

It will then migrate the request **2017-08-31** to **2017-08-01**.

While this is a simple example you can see the power with these migrations o create simple steps to migrate your current version of the api
to an older version of the API very easily.

## Installation

```bash
composer require lukepolo/laravel-api-migrations
```

### Service Provider & Facade

This package supports Laravel 5.5 autoloading so the service provider and facade will be loaded automatically. 

If you are using an earlier version of Laravel or have autoloading disabled you need to add the service provider and facade to `config/app.php`.

```php
'providers' => [
    ...
    \LukePOLO\LaravelApiMigrations\ServiceProvider::class,
]
```

```php
'aliases' => [
    ...
    'LaravelApiMigrations' => \LukePOLO\LaravelApiMigrations\Facades\LaravelApiMigrations::class,
]
```

### Middleware

Add the middleware to your Http Kernel `app/Http/Kernel.php`.

You have a couple of choices where to put this, recommenced under the api middleware!

```php
protected $middlewareGroups = [
    'api' => [
        ...
        \LukePOLO\LaravelApiMigrations\LaravelApiMigrationsMiddleware::class,
    ];
]
```

### Configuration

Run the following Artisan command to publish the package configuration to `config/request-migrations.php`.

```bash
php artisan vendor:publish --provider="LukePOLO\LaravelApiMigrations\ServiceProvider" --tag=config
```

## Usage

### Creating a Release

You can generate a new release using the Artisan CLI.

```shell
php artisan make:api-release
```

### Creating a Migration

You can generate a new api migration using the Artisan CLI.

```shell
php artisan make:api-migration ExampleMigration
```

The command will generate a api migration and publish it to `App/Http/ApiMigrations/V{VersionNumber}/Release_{YYYY_MM_DD}/{MigrationName}`.

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

### Versioning
 
You should use the artisan commands above to create releases, including your current release. You can also set your current versions in the config. 

You can tag your current versions in your config by setting it up like this : 
```php
    'current_versions' => [
        'V1' => '2017-01-31',
    ],
```

``### User Version Pinning
With version pinning you can automatically keep users to that API and allow them to upgrade to your latest version at their
convince. Once once your user hits your api for the first time it will set the most current version.

```bash
php artisan vendor:publish --provider="LukePOLO\LaravelApiMigrations\ServiceProvider" --tag=migrations
```

!!!! NOTE !!!!!

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
