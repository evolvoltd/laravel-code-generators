# Laravel 5.5 code generator from database tables

## About

The `laravel-code-generators` package allows you to create Models, Controllers, API Routes and Policies from existing database table structure.

It will fasten initial application setup, so you can advance straight to application logic.

## Installation

Require the `evolvo/laravel-code-generators` package in your `composer.json` and update your dependencies:
```sh
$ composer require evolvo/laravel-code-generators
```

add 
```sh
Evolvo\LaravelCodeGenerators\LaravelCodeGeneratorsServiceProvider::class
```
to config/app.php 'providers' array

## Usage

1. Create your migrations and run them using `php artisan migrate`.
2. Run `php artisan scaffold table_name` to generate code for all existing table_name table