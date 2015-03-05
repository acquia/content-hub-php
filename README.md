# Content Services Client for PHP

[![Build Status](https://magnum.travis-ci.com/acquia/content-service-client-php.svg?token=PH71WkhMufTnsVvCU5rV)](https://magnum.travis-ci.com/acquia/content-service-client-php)

A PHP Client for consuming the [Content Services API](https://github.com/acquia/content-service-api)

## Installation

Content Services Client Library can be installed with Composer by adding it as a dependency to your project's composer.json file. To start using composer follow these steps:

#### Install Composer:

```sh
 $ curl -sS https://getcomposer.org/installer | php
 $ mv ./composer.phar ~/bin/composer # or /usr/local/bin/composer
```

#### Create composer.json file on root of your project:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/acquia/content-service-client-php"
        }
    ],
    "require": {
        "acquia/content-service-client-php": "*"
    }
}
```

#### Install the package:
```sh
 $ composer install
```

## Usage

#### CRUD Operations

```php
use Acquia\ContentServicesClient\Entity;
use Acquia\ContentServicesClient\ContentServices;

$client = new ContentServices(['base_url' => 'http://localhost:5000']);

// Create an Entity
$entity = new Entity();
$entity->setUuid('00000000-0000-0000-0000-000000000000');
$entity->setType('product');
$client->createEntity($entity);

// Get Entity's Uuid
$uuid = $entity->getUuid();

// Read an Enity
$entity = $client->readEntity($uuid);
```
