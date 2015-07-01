<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\Entities;
use Acquia\ContentHubClient\Entity;
use Acquia\ContentHubClient\Asset;
use Acquia\ContentHubClient\Attribute;

class EntitiesTest extends \PHPUnit_Framework_TestCase
{
  private function getData()
  {
      return [
          'entities' => [
              [
                  "uuid" => "00000000-0000-0000-0000-000000000000",
                  "origin" => "11111111-0000-0000-0000-000000000000",
                  "type" => "product",
                  "created" => "2014-12-21T20:12:11+00:00Z",
                  "modified" => "2014-12-21T20:12:11+00:00Z",
                  "attributes" => [
                      "title" => [
                          "type" => "string",
                          "value" => [
                              "en" => "A",
                              "hu" => "B",
                              "und" => "C",
                          ],
                      ],
                  ],
                  "assets" => [
                      [
                          "url" => "http://acquia.com/sites/default/files/foo1.png",
                          "replace-token" => "[acquia-logo-1]",
                      ],
                      [
                          "url" => "http://acquia.com/sites/default/files/bar1.png",
                          "replace-token" => "[acquia-thumb-1]",
                      ],
                  ],
              ],
              [
                  "uuid" => "22222222-0000-0000-0000-000000000000",
                  "origin" => "33333333-0000-0000-0000-000000000000",
                  "type" => "product",
                  "created" => "2014-12-21T20:12:11+00:00Z",
                  "modified" => "2014-12-21T20:12:11+00:00Z",
                  "attributes" => [
                      "title" => [
                          "type" => "string",
                          "value" => [
                              "en" => "A",
                              "hu" => "B",
                              "und" => "C",
                          ],
                      ],
                  ],
                  "assets" => [
                      [
                          "url" => "http://acquia.com/sites/default/files/foo2.png",
                          "replace-token" => "[acquia-logo-2]",
                      ],
                      [
                          "url" => "http://acquia.com/sites/default/files/bar2.png",
                          "replace-token" => "[acquia-thumb-2]",
                      ],
                  ],
              ]
          ]
      ];

  }

  public function testCreateEntities()
  {
      $data = $this->getData()['entities'][0];

      $entities = new Entities();

      $entity = new Entity();
      $entity->setUuid($data['uuid']);
      $entity->setType($data['type']);
      $entity->setCreated($data['created']);
      $entity->setOrigin($data['origin']);
      $entity->setModified($data['modified']);

      // Adding Assets
      $assets = [
        new Asset($data['assets'][0]),
        new Asset($data['assets'][1])
      ];
      $entity->setAssets($assets);

      // Adding Attributes
      $attribute = new Attribute($data['attributes']['title']['type']);
      $attribute->setValues($data['attributes']['title']['value']);
      $attributes = [
        'title' => $attribute
      ];
      $entity->setAttributes($attributes);

      $entities->addEntity($entity);
      $this->assertEquals($entity, $entities->getEntity($entity->getUuid()));

      // Adding second Entity.
      $data = $this->getData()['entities'][1];

      $entity = new Entity();
      $entity->setUuid($data['uuid']);
      $entity->setType($data['type']);
      $entity->setCreated($data['created']);
      $entity->setOrigin($data['origin']);
      $entity->setModified($data['modified']);

      // Adding Assets
      $assets = [
        new Asset($data['assets'][0]),
        new Asset($data['assets'][1])
      ];
      $entity->setAssets($assets);

      // Adding Attributes
      $attribute = new Attribute($data['attributes']['title']['type']);
      $attribute->setValues($data['attributes']['title']['value']);
      $attributes = [
        'title' => $attribute
      ];
      $entity->setAttributes($attributes);

      $entities->addEntity($entity);
      $this->assertEquals($entity, $entities->getEntity($entity->getUuid()));

      $uuid = '66666666-0000-0000-0000-000000000000';
      $this->assertFalse($entities->getEntity($uuid));

      $entity->setUuid($uuid);
      $entities->addEntity($entity);
      $this->assertEquals($entity, $entities->getEntity($uuid));

      $entities->removeEntity($uuid);
      $this->assertFalse($entities->getEntity($uuid));

      foreach ($entities->getEntities() as $entity) {
          $this->assertInstanceOf('Acquia\ContentHubClient\Entity', $entity);
      }

      $data = $this->getData();
      $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
      $this->assertJson($json, $entities->json());
  }
}
