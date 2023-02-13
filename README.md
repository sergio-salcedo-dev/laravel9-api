# Laravel 9 - Backend API

***

##Description

Given the following ER model:

- The entity "store" is defined by and ID and a name
- The entity "product" is defined by and ID and a name
- Many-to-many relationship between Store-Product
- The pivot table between stores and products has the attribute "stock"

To do:

- Using migrations, create the tables indicated in the model.
- Model the classes using Eloquent for it. Add relationships between models, and scopes to facilitate queries.
- Implement the following endpoints using a RESTFUL API:
    - List of stores with number of products of each one
    - Description of a store, with the list of its products and quantity
    - Creation of a store. Possibility of passing a collection or array of products to store them in the database.
    - Edition of a store.
    - Deletion of a store.
- Add a product sale endpoint, with a warning in the response if the store is about to run out of stock, or if the
  operation is impossible due to lack of stock.

***

## Setup Instructions

Install the dependencies with composer ([composer installation](https://getcomposer.org/))

```sh
composer install
```

Start the development server with Laravel Sail (requires [docker](https://www.docker.com/) to be installed and running)

```sh
./vendor/bin/sail up -d
```

Stop & remove containers

```sh
./vendor/bin/sail down
```

INFO Server running on [http://localhost] or [http://0.0.0.0:80].

## Commands

```sh
./vendor/bin/sail artisan migrate
```

```sh
./vendor/bin/sail artisan migrate --seed
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
./vendor/bin/sail artisan tinker
```

***

# Composer packages

## Complete PHPDocs, directly from the source

[IDE Helper Generator for Laravel](https://github.com/barryvdh/laravel-ide-helper#automatic-PHPDocs-for-models)

## Carbon - A simple PHP API extension for DateTime.

[https://carbon.nesbot.com/](https://carbon.nesbot.com/)

### Postman config

##### Headers:

KEY | VALUE
```Accept          application/json```
