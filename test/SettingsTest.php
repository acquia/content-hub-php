<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\ContentHub;
use Acquia\ContentHubClient\User;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class SettingsTest extends \PHPUnit_Framework_TestCase
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
            "uuid" => "someuser",
            "created" => "2014-12-21T20:12:11+00:00Z",
            "modified" => "2014-12-21T20:12:11+00:00Z",
            "webhooks" => [
                [
                    "url" => "http://example1.com/webhooks",
                    "uuid" => "00000000-0000-0000-0000-000000000000",
                ],
                [
                    "url" => "http://example2.com/webhooks",
                    "uuid" => "11111111-0000-0000-0000-000000000000",
                ],
            ],
            "clients" => [
                [
                    "name" => "My Client Site 1",
                    "uuid" => "22222222-0000-0000-0000-000000000000",
                ],
            ],
            "success" => 1,
        ];
    }

    public function testReadSettings()
    {
        // Setup
        $data = $this->setData();
        $responses = [
            new Response(200, [], json_encode($data)),
        ];
        $client = $this->getClient($responses);

        // Read Settings
        $settings = $client->getSettings();
        $this->assertEquals($data['uuid'], $settings->getUuid());
        $this->assertEquals($data['created'], $settings->getCreated());
        $this->assertEquals($data['modified'], $settings->getModified());
        $this->assertEquals($data['webhooks'], $settings->getWebhooks());
        $this->assertEquals($data['clients'], $settings->getClients());
        $this->assertEquals($data['success'], $settings->success());

        $this->assertEquals($data['webhooks'][0], $settings->getWebhook('http://example1.com/webhooks'));
        $this->assertFalse($settings->getWebhook('http://example.com/webhook'));
        $this->assertEquals($data['clients'][0], $settings->getClient('My Client Site 1'));
        $this->assertFalse($settings->getClient('My Client Site 2'));

    }

    public function testRegisterClients()
    {
        // Setup
        $data = $this->setData()['clients'][0];
        $responses = [
            new Response(200, [], json_encode($data))
        ];
        $client = $this->getClient($responses);

        // Add a Client
        $registered_client = $client->register('My Client Site 1');
        $this->assertEquals($data, $registered_client);
    }

    public function testAddWebhook()
    {
        // Setup
        $data = $this->setData()['webhooks'][0];
        $responses = [
            new Response(200, [], json_encode($data)),
        ];
        $client = $this->getClient($responses);

        // Set a Webhook
        $webhook = $client->addWebhook('http://example1.com/webhooks');
        $this->assertEquals($data, $webhook);
    }

    public function testDeleteWebhook()
    {
        // Setup
        $data = [
            'success' => 1,
        ];
        $responses = [
            new Response(200, [], json_encode($data)),
        ];
        $client = $this->getClient($responses);

        // Deletes a Webhook
        $webhook = $client->deleteWebhook('http://example1.com/webhooks');
        $this->assertEquals($data, json_decode($webhook->getBody(), TRUE));
    }
}