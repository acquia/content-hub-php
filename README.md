# Content Services Client for PHP

A PHP Client for consuming the [Content Services API](https://github.com/acquia/content-service-api)

## Installation

Content Services Client can be installed with Composer by adding it as a dependency to your project's composer.json file.

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

## Usage

### CRUD Operations

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