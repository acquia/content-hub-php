<?php

namespace Acquia\ContentServicesClient\test;

use Acquia\ContentServicesClient\Entity;
use Acquia\ContentServicesClient\ContentServices;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

class ContentServicesTest extends \PHPUnit_Framework_TestCase
{
    private function setData()
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
                    "replacetoken" => "[acquia-logo]",
                ],
                [
                    "url" => "http://acquia.com/sites/default/files/bar.png",
                    "replacetoken" => "[acquia-thumb]",
                ],
            ],
        ];
    }

    public function testCreateEntity()
    {
        $data = $this->setData();
        $client = new ContentServices(['base_url' => 'http://example.com']);

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Create an Entity
        $entity = new Entity();
        $entity->setUuid($data['uuid']);
        $entity->setType($data['type']);
        $response = $client->createEntity($entity);
        $this->assertEquals($mockResponse->json(), $response->json());
        $this->assertEquals($mockResponse, $response);
    }

    public function testReadEntity()
    {
        $data = $this->setData();
        $client = new ContentServices(['base_url' => 'http://example.com']);

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Read an Entity
        $entity = $client->readEntity('00000000-0000-0000-0000-000000000000');
        $this->assertEquals(6, count($entity));
    }

    public function testUpdateEntity()
    {
        $data = [
            'uuid' => '00000000-0000-0000-0000-000000000000',
            "type" => "blog",
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
                    "replacetoken" => "[acquia-logo]",
                ],
                [
                    "url" => "http://acquia.com/sites/default/files/bar.png",
                    "replacetoken" => "[acquia-thumb]",
                ],
            ],
        ];
        $client = new ContentServices(['base_url' => 'http://example.com']);

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Update an Entity
        $entity = new Entity();
        $entity->setType('blog');
        $response = $client->updateEntity($entity, '00000000-0000-0000-0000-000000000000');
        $this->assertEquals($mockResponse->json(), $response->json());
        $this->assertEquals($mockResponse, $response);
    }

    public function testDeleteEntity()
    {
        $client = new ContentServices(['base_url' => 'http://example.com']);

        $mock = new Mock();

        $mockResponse = new Response(200);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Delete an Entity
        $response = $client->deleteEntity('00000000-0000-0000-0000-000000000000');
        $this->assertEquals(200, $response->getStatusCode());
    }
}
