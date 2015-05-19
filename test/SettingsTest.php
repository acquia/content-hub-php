<?php
/**
 * @file
 * Test for User Class.
 */

namespace Acquia\ContentServicesClient\test;

use Acquia\ContentServicesClient\ContentServices;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use Acquia\ContentServicesClient\User;

class SettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \Acquia\ContentServicesClient\ContentServices
     */
    private function getClient()
    {
        return new ContentServices('public', 'secret', 'origin');
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

    public function testReadUser()
    {
        $data = $this->setData();
        $client = $this->getClient();

        $mock = new Mock();

        $mockResponseBody = Stream::factory(json_encode($data));
        $mockResponse = new Response(200, [], $mockResponseBody);
        $mock->addResponse($mockResponse);
        $client->getEmitter()->attach($mock);

        // Read an Entity
        $settings = $client->getSettings();
        $this->assertEquals($data['uuid'], $settings->getUuid());
        $this->assertEquals($data['created'], $settings->getCreated());
        $this->assertEquals($data['modified'], $settings->getModified());
        $this->assertEquals($data['webhooks'], $settings->getWebhooks());
        $this->assertEquals($data['clients'], $settings->getClients());
        $this->assertEquals($data['success'], $settings->success());
    }
}