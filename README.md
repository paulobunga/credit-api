# Credit API
[![Build Status](https://travis-ci.org/laravel/lumen-framework.svg)](https://travis-ci.org/laravel/lumen-framework)
[![Latest Stable Version](https://img.shields.io/packagist/v/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)
[![License](https://img.shields.io/packagist/l/laravel/framework)](https://packagist.org/packages/laravel/lumen-framework)

## Introduction

Simple Restful API project integrated few useful libraries

Framework: [Lumen](https://lumen.laravel.com/docs/8.x)

API Wrapper: [Dingo/Api](https://github.com/dingo/api/wiki)

Permission Library: [Spatie/laravel-permission](https://spatie.be/docs/laravel-permission/v4/introduction) 

Query filter: [Spatie/laravel-query-builder](https://spatie.be/index.php/docs/laravel-query-builder/v3/introduction)

OneSignal Notification: [laravel-notification-channels/onesignal](https://github.com/laravel-notification-channels/onesignal)

Pusher Channel: [pusher/pusher-http-php](https://github.com/pusher/pusher-http-php)

DTO(json column): [spatie/data-transfer-object](https://github.com/spatie/data-transfer-object)

Setting: [spatie/laravel-settings](https://github.com/spatie/laravel-settings)

## Installation 

```cmd
cp .env.example .env

composer install

php artisan key:generate

php artisan jwt:secret

php artisan migrate --seed

npm i

npm run prod
```

## Queue Configuration

```
pm2 start queue.yml
```

For more instructions, view [PM2](https://pm2.keymetrics.io/docs/usage/quick-start).

## Public API Document

```cmd
php artisan scribe:generate
```

For more information, view [Scribe](https://scribe.knuckles.wtf/laravel/).

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).