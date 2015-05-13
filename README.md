# Plexus Client for PHP

[![Build Status](https://magnum.travis-ci.com/acquia/content-service-client-php.svg?token=PH71WkhMufTnsVvCU5rV)](https://magnum.travis-ci.com/acquia/content-service-client-php)

A PHP Client for [Plexus](https://github.com/acquia/plexus)

## Installation

Plexus Client for PHP can be installed with Composer by adding it as a
dependency to your project's composer.json file. To start using composer follow
these steps:

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
use Acquia\ContentServicesClient\Attribute;
use Acquia\ContentServicesClient\Asset;
use Acquia\ContentServicesClient\ContentServices;

$api = 'AAAAAAAAAAAAAAAAAAAA';
$secret = 'BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB';
$origin = '11111111-0000-0000-0000-000000000000';

$client = new ContentServices($api, $secret, $origin, ['base_url' => 'http://localhost:5000']);

// Create a Plexus Entity Object
$entity = new Entity();
$entity->setUuid('00000000-0000-0000-0000-000000000000');
$entity->setType('product');
$entity->setOrigin($origin);
$entity->setCreated('2014-12-21T20:12:11+00:00Z');
$entity->setModified('2014-12-21T20:12:11+00:00Z');

// Adding Attributes
$attribute = new Attribute(Attribute::TYPE_STRING);
$attribute->setValue('nothing', 'en');
$attribute->setValue('nada', 'es');
$attribute->setValue('nothing');
$entity->setAttribute('name', $attribute);

$attribute = new Attribute(Attribute::TYPE_INTEGER);
$attribute->setValue(4);
$entity->setAttribute('age', $attribute);

$attribute = new Attribute(Attribute::TYPE_STRING);
$attribute->setValue('[asset-1]');
$entity->setAttribute('image', $attribute);

// Adding Assets
$asset = new Asset();
$asset->setUrl('http://placehold.it/100');
$asset->setReplaceToken('[asset-1]');
$entity->addAsset($asset);

// Get Json representation of the entity
$entity->json();

// Create an Entity in Plexus.
// The variable $resource_url should contain a link to the Plexus Entity in json format.
$resource_url = 'http://plexus.acquia.com/entity/00000000-0000-0000-0000-000000000000';
$client->createEntity($resource_url);

// Update an Entity in Plexus.
// The variable $resource_url should contain a link to the Plexus Entity in json format.
$resource_url = 'http://plexus.acquia.com/entity/00000000-0000-0000-0000-000000000000';
$uuid = '00000000-0000-0000-0000-000000000000';
$client->updateEntity($resource_url, $uuid);

// Delete an Entity in Plexus
$client->deleteEntity($uuid);

// Read an Entity
$entity = $client->readEntity($uuid);

// Get Entity's Uuid
$uuid = $entity->getUuid();

// Get Assets
$assets = $entity->getAssets();

// Get Attributes
$attributes = $entity->getAttributes();

// Get a particular attribute
$attribute = $entity->getAttribute('name');

// Get the URL of the image attribute, as set above.
$token = $entity->getAttribute('image')->getValue();
$url = $entity->getAsset($token)->getUrl();

// Get the attribute name in spanish.
$name = $entity->getAttribute('name')->getValue('es');

```
