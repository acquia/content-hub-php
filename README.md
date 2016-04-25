# Acquia Content Hub Client for PHP

[![Build Status](https://travis-ci.org/acquia/content-hub-php.svg)](https://travis-ci.org/acquia/content-hub-php)

A PHP Client library to consume the Acquia Content Hub API.

## Version Information

* `0.6.x` branch: Uses guzzle version `~5.0`. Drupal 7 [content hub module](https://docs.acquia.com/content-hub) depends upon builds against this branch.
* `master` branch: Uses guzzle version `~6.0`. Drupal 8 content hub work, that is in progress at the moment, depends upon builds against this branch.

## Installation

Install the latest version with [Composer](https://getcomposer.org/):

```bash
$ composer require acquia/content-hub-php
```

## Usage

#### Register the application

Applications must register themselves with Content Hub so that they are assigned
a unique identifier. The identifier is required by most API endpoints and is
used as the "origin" of entities that are created by the application and
published to the hub.

```php
<?php

use Acquia\ContentHubClient\ContentHub;

// The URL to the Content Hub instance, provided by Acquia. Note that us-east-1
// might be replaced by a region that is within your geographic proximity.
$url = 'https://us-east-1.content-hub.acquia.com';

// The API key and secret key provided by Acquia that are used to authenticate
// requests to Content Hub.
$apiKey    = 'AAAAAAAAAAAAAAAAAAAA';
$secretKey = 'BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB';

$client = new ContentHub($apiKey, $secretKey, '', ['base_url' => $url]);

// Register the application (or client site) with Content Hub. The parameter
// passed to this method is the human-readable name of the application.
$clientSite = $client->register('myclientsite');

// Stores the application's unique identifier that is assigned by Content Hub.
$clientId = $clientSite['uuid'];

```

#### Add a webhook receiver endpoint

Content Hub sends push notifications and status messages for asynchronous
operations via webhooks. Even if your application doesn't require near-real-time
content updates, you should implement a webhook receiver endpoint so that you
have visibility into what is happening during asynchronous operations.

```php
<?php

// Add the endpoint that received webhooks posted from Content Hub.
$webhook = $client->addWebhook('http://example.com/content-hub/webhooks');

// Deleting the webhook receiver endpoint so that the application will no longer
// receive webhooks.
$client->deleteWebhook($webhook['uuid']);

```

#### Creating entities

```php
<?php

use Acquia\ContentHubClient\ContentHub;
use Acquia\ContentHubClient\Entities;
use Acquia\ContentHubClient\Entity;
use Acquia\ContentHubClient\Attribute;
use Acquia\ContentHubClient\Asset;

$client = new ContentHub($apiKey, $secretKey, $clientId, ['base_url' => $url]);

// The unique identifier of the entity, usually a randomly generated UUID.
// See https://github.com/ramsey/uuid to simplify UUID generation in PHP.
$uuid = '00000000-0000-0000-0000-000000000000'

// Build the entity, add required metadata
$entity = new Entity();
$entity->setUuid($uuid);
$entity->setType('product');
$entity->setOrigin($clientId);
$entity->setCreated('2014-12-21T20:12:11+00:00Z');
$entity->setModified('2014-12-21T20:12:11+00:00Z');

// Add attributes
$attribute = new Attribute(Attribute::TYPE_STRING);
$attribute->setValue('nothing', 'en');
$attribute->setValue('nada', 'es');
$attribute->setValue('nothing');
$entity->setAttribute('name', $attribute);

$attribute = new Attribute(Attribute::TYPE_INTEGER);
$attribute->setValue(4);
$entity->setAttribute('age', $attribute);

// Add references to binary assets, e.g. images.
$attribute = new Attribute(Attribute::TYPE_STRING);
$attribute->setValue('[asset-1]');
$entity->setAttribute('image', $attribute);

$asset = new Asset();
$asset->setUrl('http://placehold.it/100');
$asset->setReplaceToken('[asset-1]');
$entity->addAsset($asset);

// Create an entity container, add the entity to it.
$entities = new Entities();
$entities->addEntity($entity);

// Render the entities in Common Data Format (CDF). This should be the payload
// returned by requests to $resourceUrl (defined below).
$cdf = $entities->json();

// Queue the entity to be added to Content Hub. An important concept in Content
// Hub is that write operations are asynchronous, meaning that the actions are
// not performed right away. In this example, a URL is passed to Content Hub
// which is expected to render the entities that you want to add to the hub in
// CDF. Content Hub receives the request and immediately returns a 202 which
// signifies that the request was received. In the background, Content Hub then
// makes a request to the URL, reads the CDF, and adds the entities. Success and
// error messages are sent via webhooks, so it is important to implement a
// webhook receiver endpoint so that you know what is going on.
$resourceUrl = 'http://example.com/path/to/cdf';
$client->createEntities($resourceUrl);

```

#### Reading entities

```php
<?php

// Get the entity from Content Hub.
$entity = $client->readEntity($uuid);

// Get the "name" attribute in English, then Spanish.
$name   = $entity->getAttribute('name')->getValue('en');
$nombre = $entity->getAttribute('name')->getValue('es');

// Get the URL of the image attribute by dereferencing the token.
$token = $entity->getAttribute('image')->getValue();
$url = $entity->getAsset($token)->getUrl();

```

#### Updating entities

```php
<?php

// Update the value of the "age" attribute from 4 to 5.
$attribute = new Attribute(Attribute::TYPE_INTEGER);
$attribute->setValue(5);
$entity->setAttribute('age', $attribute);

// Updating entities is also an asynchronous operation, so it may take a couple
// of seconds for the changes to be reflected in the hub.
$client->updateEntity($resourceUrl, $uuid);

```

#### Deleting entities

```php
<?php

// Delete the entity by passing it's UUID. Delete operations are asynchronous,
// so it may take a couple of seconds for entities to be purged from the hub.
$client->deleteEntity($uuid);

```
