# Credit API

[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Total Downloads](https://img.shields.io/packagist/dt/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://img.shields.io/packagist/l/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)

## Introduction

Simple Restful API project integrated few useful library, such as [Dingo/Api](https://github.com/dingo/api/wiki), [Spatie/laravel-permission](https://spatie.be/docs/laravel-permission/v4/introduction), [Spatie/laravel-query-builder](https://spatie.be/index.php/docs/laravel-query-builder/v3/introduction).

## Installation 

```cmd
cp .env.example .env

composer install

php artisan key:generate

php artisan jwt:secret

php artisan migrate --seed
```

## Queue Configuration

```
pm2 start queue.yml
```

For more instructions, view [PM2](https://pm2.keymetrics.io/docs/usage/quick-start).

## Generate Api Document

```cmd
php artisan scribe:generate
```

For more information, view [Scribe](https://scribe.knuckles.wtf/laravel/).

## License

The Lumen framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).