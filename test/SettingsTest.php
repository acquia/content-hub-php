<?php
/**
 * @file
 * Test for User Class.
 */

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\ContentHub;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use Acquia\ContentHubClient\User;

class SettingsTest extends \PHPUnit_Framework_TestCase
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
        $data = $this->setData();
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

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
        $data = $this->setData()['clients'][0];
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);
        // Add a Client
        $registered_client = $client->register('My Client Site 1');
        $this->assertEquals($data, $registered_client);
    }

    public function testAddWebhook()
    {
      $data = $this->setData()['webhooks'][0];
      $client = $this->getClient();

      $mock = new Mock();

      $mockResponseBody = Stream::factory(json_encode($data));
      $mockResponse = new Response(200, [], $mockResponseBody);
      $mock->addResponse($mockResponse);
      $client->getEmitter()->attach($mock);

      // Set a Webhook
      $webhook = $client->addWebhook('http://example1.com/webhooks');
      $this->assertEquals($data, $webhook);

    }

    public function testDeleteWebhook()
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

      // Deletes a Webhook
      $webhook = $client->deleteWebhook('http://example1.com/webhooks');
      $this->assertEquals($data, $webhook->json());

    }
}