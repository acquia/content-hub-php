# Acquia Content Hub Client for PHP

[![Build Status](https://travis-ci.org/acquia/content-hub-php.svg)](https://travis-ci.org/acquia/content-hub-php)

A PHP Client library to consume the Acquia Content Hub API.

## Installation

Acquia Content Hub Client for PHP can be installed with Composer by adding it as a
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
            "url": "https://github.com/acquia/content-hub-php"
        }
    ],
    "require": {
        "acquia/content-hub-php": "*"
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
use Acquia\ContentHubClient\Entities;
use Acquia\ContentHubClient\Entity;
use Acquia\ContentHubClient\Attribute;
use Acquia\ContentHubClient\Asset;
use Acquia\ContentHubClient\ContentHub;

$api = 'AAAAAAAAAAAAAAAAAAAA';
$secret = 'BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB';
$origin = '';

$client = new ContentHub($api, $secret, $origin, ['base_url' => 'http://localhost:5000']);

// Register a client
$client_site = $client->register('myclientsite');

// The registration returns an origin that will be used in following requests.
$origin = $client_site['uuid'];
$client = new ContentHub($api, $secret, $origin, ['base_url' => 'http://localhost:5000']);

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

// Group Entities
$entities = new Entities();
$entities->addEntity($entity);

// Get Json
$entities->json();

// Create an Entity in Plexus.
// The variable $resource_url should contain a link to the Plexus Entity in json format.
$resource_url = 'http://plexus.acquia.com/entity/00000000-0000-0000-0000-000000000000';
$client->createEntities($resource_url);

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

// Get All Attributes
$attributes = $entity->getAttributes();

// Get a particular attribute
$attribute = $entity->getAttribute('name');

// Get the URL of the image attribute, as set above.
$token = $entity->getAttribute('image')->getValue();
$url = $entity->getAsset($token)->getUrl();

// Get the attribute name in spanish.
$name = $entity->getAttribute('name')->getValue('es');

// Adding a Webhook
$webhook = $client->addWebhook('http://example.com/webhooks');

// Deleting the same webhook
$client->deleteWebhook($webhook['uuid']);

```
