<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\Entity;
use Acquia\ContentHubClient\ContentHub;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

class ContentHubTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \Acquia\ContentHubClient\ContentHub
     */
    private function getClient()
    {
        return new ContentHub('public', 'secret', 'origin');
    }

    private function setData()
    {
        return [
            'data' => [
                'uuid' => '00000000-0000-0000-0000-000000000000',
                'origin' => '11111111-0000-0000-0000-000000000000',
                'data' => [
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
                ],
            ],
        ];
    }

    private function setDefinition()
    {
        return [
          'children' => [
              0 => '/settings',
              1 => '/register',
              2 => '/entities',
              3 => '/ping',
              4 => '/elastic',
          ],
        ];
    }

    private function setListOfEntities()
    {
        return [
            'success' => true,
            'total' => 2,
            'data' => [
                0 => [
                    'uuid' => '00000000-0000-0000-0000-000000000000',
                    'origin' => '11111111-1111-1111-1111-111111111111',
                    'modified' => '2015-06-29T12:15:36-04:00',
                    'type' => 'node',
                    'attributes' => [
                        'body' => [
                            'und' => '{"summary":"","value":"Custom table","format":"filtered_html"}',
                        ],
                        'description' => NULL,
                        'field_tags' => [
                            'und' => [
                                0 => '88fa41e8-b959-41f1-aaa9-e9017936d8ca',
                                1 => '7f0931f6-2d04-4488-9eec-fbd81e604ce5',
                            ],
                        ],
                        'status' => [
                            'und' => 1,
                        ],
                        'title' => [
                            'und' => 'A new custom table',
                        ],
                    ],
                ],
                1 => [
                    'uuid' => '00000000-1111-0000-0000-000000000000',
                    'origin' => '11111111-1111-1111-1111-111111111111',
                    'modified' => '2015-07-01T14:18:29-04:00',
                    'type' => 'node',
                    'attributes' => [
                        'body' => [
                            'und' => '{"summary":"","value":"The following is a Custom bench for Boston.","format":"filtered_html"}',
                        ],
                        'description' => NULL,
                        'field_tags' => [
                            'und' => [
                                0 => '94e38271-df71-4da6-ae3c-dce143244b65',
                            ],
                        ],
                        'status' => [
                            'und' => 1,
                        ],
                        'title' => [
                            'und' => 'New Custom Bench 2',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testPing()
    {
        $data = [
            'success' => 1,
        ];
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Pinging the service
        $response = $client->ping();
        $this->assertEquals($data, $response->json());
    }

    public function testDefinition()
    {
        $data = $this->setDefinition();

        $client = $this->getClient();

        $mock = new Mock();
        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        $response = $client->definition();
        $this->assertEquals($data, $response);
    }

    public function testClientByName()
    {
        $data = [
            'name' => 'mysite',
            'uuid' => '00000000-0000-0000-0000-000000000000',
        ];

        $client = $this->getClient();

        $mock = new Mock();
        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        $response = $client->getClientByName('mysite');
        $this->assertEquals($data, $response);
    }

    public function testCreateEntity()
    {
        $data = [
            'success' => true,
        ];
        $resource = 'http://acquia.com/content_hub_connector/node/00000000-0000-0000-0000-000000000000';
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Create an Entity
        $response = $client->createEntity($resource);
        $this->assertEquals($mockResponse->json(), $response->json());
        $this->assertEquals($mockResponse, $response);

        // Create one or more entities
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);
        $response = $client->createEntities($resource);
        $this->assertEquals($mockResponse->json(), $response->json());
        $this->assertEquals($mockResponse, $response);

    }

    public function testReadEntity()
    {
        $data = $this->setData();
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Read an Entity
        $entity = $client->readEntity('00000000-0000-0000-0000-000000000000');
        $this->assertEquals(6, count($entity));
        $this->assertEquals($data['data']['data'], (array) $entity);
    }

    public function testUpdateEntity()
    {
        $data = [
            'success' => true,
        ];
        $resource = 'http://acquia.com/content_hub_connector/node/00000000-0000-0000-0000-000000000000';
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Update an Entity
        $response = $client->updateEntity($resource, '00000000-0000-0000-0000-000000000000');
        $this->assertEquals($mockResponse->json(), $response->json());
        $this->assertEquals($mockResponse, $response);

        // Test Update Entities (one or more)
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);
        $response = $client->updateEntities($resource);
        $this->assertEquals($mockResponse->json(), $response->json());
        $this->assertEquals($mockResponse, $response);
    }

    public function testDeleteEntity()
    {
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponse = new Response(200);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Delete an Entity
        $response = $client->deleteEntity('00000000-0000-0000-0000-000000000000');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testListEntities()
    {
        $data = $this->setListOfEntities();
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Listing entities
        $options = [
            'limit'  => 20,
            'type'   => 'node',
            'origin' => '11111111-1111-1111-1111-111111111111',
            'fields' => 'status,title,body,field_tags,description',
            'filters' => [
                'status' => 1,
                'title' => 'New*',
                'body' => '/Custom/',
            ],
        ];
        $response = $client->listEntities($options);
        $this->assertEquals($data, $response);
    }
}
