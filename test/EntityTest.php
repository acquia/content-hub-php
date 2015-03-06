<?php

namespace Acquia\ContentServicesClient\test;

use Acquia\ContentServicesClient\Entity;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    private function getData()
    {
        return [
            'uuid' => '00000000-0000-0000-0000-000000000000',
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
        $entity->setModified($data['modified']);
        $entity->setAssets($data['assets']);
        $entity->setAttributes($data['attributes']);
        $this->assertEquals($data['uuid'], $entity->getUuid());
        $this->assertEquals($data['type'], $entity->getType());
        $this->assertEquals($data['created'], $entity->getCreated());
        $this->assertEquals($data['modified'], $entity->getModified());
        $this->assertEquals($data['assets'], $entity->getAssets());
        $this->assertEquals("http://acquia.com/sites/default/files/foo.png", $entity->getAssets()[0]['url']);
        $this->assertEquals($data['attributes'], $entity->getAttributes());
    }
}
