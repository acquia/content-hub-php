<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\Entity;
use Acquia\ContentHubClient\Asset;
use Acquia\ContentHubClient\Attribute;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    private function getData()
    {
        return [
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
                "empty1" => [
                    "type" => "boolean",
                    "value" => [
                        "und" => null,
                    ]
                ],
            ],
            "assets" => [
                [
                    "url" => "http://acquia.com/sites/default/files/foo.png",
                    "replace-token" => "[acquia-logo]",
                ],
                [
                    "url" => "http://acquia.com/sites/default/files/bar.png",
                    "replace-token" => "[acquia-thumb]",
                ],
            ],
        ];
    }

    public function testCreateEntity()
    {
        $data = $this->getData();

        $entity = new Entity();
        $entity->setUuid($data['uuid']);
        $entity->setType($data['type']);
        $entity->setCreated($data['created']);
        $entity->setOrigin($data['origin']);
        $entity->setModified($data['modified']);
        $this->assertEquals($data['uuid'], $entity->getUuid());
        $this->assertEquals($data['type'], $entity->getType());
        $this->assertEquals($data['created'], $entity->getCreated());
        $this->assertEquals($data['origin'], $entity->getOrigin());
        $this->assertEquals($data['modified'], $entity->getModified());

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
        $attribute = new Attribute($data['attributes']['empty1']['type']);
        $attribute->setValues($data['attributes']['empty1']['value']);
        $attributes = [
          'empty1' => $attribute
        ];
        $entity->setAttributes($attributes);

        // Checks
        $this->assertEquals($assets, $entity->getAssets());
        $this->assertEquals("http://acquia.com/sites/default/files/foo.png", $entity->getAssets()[0]['url']);

        // Adding the same asset does not mess up with the asset's list.
        $entity->addAsset(new Asset($data['assets'][0]));
        $this->assertEquals($assets, $entity->getAssets());

        $this->assertEquals($attributes, $entity->getAttributes());

        // Adding / Removing Assets
        $asset_array = [
            'url' => 'http://acquia.com/sites/default/files/foo-bar.png',
            'replace-token' => '[acquia-foobar]'
        ];

        $asset = new Asset($asset_array);
        $entity->addAsset($asset);
        $myasset = $entity->getAsset($asset_array['replace-token']);
        $this->assertEquals($asset_array['url'], $myasset->getUrl());
        $this->assertEquals($asset_array['replace-token'], $myasset->getReplaceToken());

        $entity->removeAsset($asset_array['replace-token']);
        $this->assertFalse($entity->getAsset($asset_array['replace-token']));

        // Adding / Removing Attributes
        $attribute_value = [
          'my_attribute' => [
              'type' => 'integer',
              'value' => [
                  'en' => '4',
                  'es' => '3',
                  'und' => 0
                ]
           ]
        ];
        $attribute = new Attribute(Attribute::TYPE_INTEGER);
        $attribute->setValues($attribute_value['my_attribute']['value']);
        $name = array_keys($attribute_value);
        $name = reset($name);
        $entity->setAttribute($name, $attribute);
        $this->assertEquals((array) $attribute, (array) $entity->getAttribute($name));

        $attribute_value['my_attribute']['value']['it'] = 400;
        $entity->setAttributeValue($name, $attribute_value['my_attribute']['value']['it'], 'it');
        $this->assertEquals($attribute_value['my_attribute']['value']['it'], $entity->getAttribute($name)->getValue('it'));

        $entity->removeAttribute($name);
        $this->assertEquals($attributes, $entity->getAttributes());

        // Handling NULL Attributes.
        $attribute = new Attribute(Attribute::TYPE_BOOLEAN);
        $attribute->setValue(NULL);
        $entity->setAttribute('empty1', $attribute);
        $this->assertEquals($attributes, $entity->getAttributes());

        $attribute = new Attribute(Attribute::TYPE_ARRAY_NUMBER);
        $entity->setAttribute('empty2', $attribute);
        $this->assertEquals($attributes, $entity->getAttributes());

        $json = json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $this->assertJson($json, $entity->json());
    }
}
