# Laravel 5.5 code generator from database tables

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Total Downloads][ico-downloads]][link-downloads]

## About

The `laravel-code-generators` package allows you to create Models, Controllers, API Routes and Policies from existing database table structure.

It will fasten initial application setup, so you can advance straight to application logic.

## Installation

Require the `evolvo/laravel-code-generators` package in your `composer.json` and update your dependencies:
```sh
$ composer require evolvo/laravel-code-generators
```

## Usage

1. Create your migrations and run them using `php artisan migrate`.
2. Run `php artisan auto-generate-code --all` to generate code for all existing tables except `migrations` and `password_resets`

OR

1. Create your migrations and run them using `php artisan migrate`.
2. Run `php artisan auto-generate-code --table=your_single_table_name` to generate code for specific table.
(this can be handy if you want to generate code during later development stages, when new database tables are needed).

```php
protected $middleware = [
    // ...
    \Barryvdh\Cors\HandleCors::class,
];
```