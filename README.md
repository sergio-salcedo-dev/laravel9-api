# Laravel 9 - Backend API

***

## Setup Instructions

Install the dependencies with composer ([composer installation](https://getcomposer.org/))

```sh
composer install
```

Start the development server with Laravel Sail (requires [docker](https://www.docker.com/) to be installed and running)

```sh
./vendor/bin/sail up
```

Stop & remove containers

```sh
./vendor/bin/sail down
```

INFO Server running on [http://localhost] or [http://0.0.0.0:80].

```sh
./vendor/bin/sail artisan migrate
```

```sh
./vendor/bin/sail artisan migrate:rollback --step=1
```

```sh
./vendor/bin/sail artisan cache:clear
```

```sh
./vendor/bin/sail artisan config:clear
```

```sh
docker network inspect bridge
```

```sh
./vendor/bin/sail artisan tinker
```

```sh
./vendor/bin/sail artisan make:seeder StoreSeeder
```

```sh
./vendor/bin/sail artisan migrate --seed
```

```sh
./vendor/bin/sail artisan make:provider RepositoryServiceProvider
```

***

# Composer packages

## Complete PHPDocs, directly from the source

[IDE Helper Generator for Laravel](https://github.com/barryvdh/laravel-ide-helper#automatic-PHPDocs-for-models)

## Carbon - A simple PHP API extension for DateTime.
[https://carbon.nesbot.com/](https://carbon.nesbot.com/)
