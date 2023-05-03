# Laravel 9 code generator from database tables

## About

The `laravel-code-generators` package allows you to generate application code based on database table structure.

It will fasten initial application setup, so you can advance straight to application logic.

What will be generated?
- Model
- Controller
- FormRequests
- Service
- API Routes
- Factory
- Tests 

Package can also generate front-end code for Vue (v2) and Angular (v2+) 

## Installation

Require the `evolvo/laravel-code-generators` package in your `composer.json` and update your dependencies:
```sh
$ composer require evolvo/laravel-code-generators
```

## Usage

1. Create your migrations and run them using `php artisan migrate`.
2. Run `php artisan scaffold table_name` to generate Laravel code based on table structure.
5. Run `php artisan scaffold table_name --vue` to generate Vue code based on table structure.
5. Run `php artisan scaffold table_name --angular` to generate Angular code based on table structure.


