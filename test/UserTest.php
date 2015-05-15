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

class UserTest extends \PHPUnit_Framework_TestCase
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
        $user = $client->getUser();
        $this->assertEquals($data['uuid'], $user->getUuid());
        $this->assertEquals($data['created'], $user->getCreated());
        $this->assertEquals($data['modified'], $user->getModified());
        $this->assertEquals($data['webhooks'], $user->getWebhooks());
    }
}