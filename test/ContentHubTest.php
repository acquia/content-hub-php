<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\ContentHub;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class ContentHubTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $responses Responses
     *
     * @return \Acquia\ContentHubClient\ContentHub
     */
    private function getClient(array $responses = [])
    {
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        return new ContentHub('public', 'secret', 'origin', ['handler' => $stack]);
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
        // Setup
        $data = [
            'success' => 1,
        ];
        $responses = [
            new Response('200', [], json_encode($data)),
        ];
        $client = $this->getClient($responses);

        // Ping the service
        $response = $client->ping();
        $body = (string) $response->getBody();
        $this->assertEquals($data, json_decode($body, TRUE));
    }

    public function testDefinition()
    {
        // Setup
        $data = $this->setDefinition();
        $responses = [
            new Response('200', [], json_encode($data)),
        ];
        $client = $this->getClient($responses);

        // Get definition
        $response = $client->definition();
        $this->assertEquals($data, $response);
    }

    public function testClientByName()
    {
        // Setup
        $data = [
            'name' => 'mysite',
            'uuid' => '00000000-0000-0000-0000-000000000000',
        ];
        $responses = [
            new Response('200', [], json_encode($data)),
        ];
        $client = $this->getClient($responses);

        // Get client by name
        $response = $client->getClientByName('mysite');
        $this->assertEquals($data, $response);
    }

    public function testCreateEntity()
    {
        // Setup
        $data = [
            'success' => true,
        ];
        $resource = 'http://acquia.com/content_hub_connector/node/00000000-0000-0000-0000-000000000000';
        $responses = [
            new Response(200, [], json_encode($data)),
            new Response(200, [], json_encode($data)),
        ];
        $client = $this->getClient($responses);

        // Create an Entity
        $response = $client->createEntity($resource);
        $body = json_decode((string) $response->getBody(), TRUE);
        $this->assertEquals($data, $body);
        $this->assertEquals($responses[0], $response);

        // Create one or more entities
        $response = $client->createEntities($resource);
        $body = json_decode((string) $response->getBody(), TRUE);
        $this->assertEquals($data, $body);
        $this->assertEquals($responses[1], $response);
    }

    public function testReadEntity()
    {
        // Setup
        $data = $this->setData();
        $responses = [
            new Response(200, [], json_encode($data)),
        ];
        $client = $this->getClient($responses);

        // Read an Entity
        $entity = $client->readEntity('00000000-0000-0000-0000-000000000000');
        $this->assertEquals(6, count($entity));
        $this->assertEquals($data['data']['data'], (array) $entity);
    }

    public function testUpdateEntity()
    {
        // Setup
        $data = [
            'success' => true,
        ];
        $resource = 'http://acquia.com/content_hub_connector/node/00000000-0000-0000-0000-000000000000';
        $responses = [
            new Response(200, [], json_encode($data)),
            new Response(200, [], json_encode($data)),
        ];
        $client = $this->getClient($responses);

        // Update an Entity
        $response = $client->updateEntity($resource, '00000000-0000-0000-0000-000000000000');
        $this->assertEquals(json_encode($data), $response->getBody());
        $this->assertEquals($responses[0], $response);

        // Test Update Entities (one or more)
        $response = $client->updateEntities($resource);
        $this->assertEquals(json_encode($data), $response->getBody());
        $this->assertEquals($responses[1], $response);
    }

    public function testDeleteEntity()
    {
        // Setup
        $responses = [
            new Response(200),
        ];
        $client = $this->getClient($responses);

        // Delete an Entity
        $response = $client->deleteEntity('00000000-0000-0000-0000-000000000000');
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testListEntities()
    {
        // Setup
        $data = $this->setListOfEntities();
        $responses = [
          new Response(200, [], json_encode($data)),
        ];
        $client = $this->getClient($responses);

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
