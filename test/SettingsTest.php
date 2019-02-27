<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 2/21/19
 * Time: 4:02 PM
 */

namespace Acquia\ContentHubClient\test;


use Acquia\ContentHubClient\Settings;
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase
{
    /**
     * @var Settings
     */
    private $settings;

    public function setUp() : void
    {
        parent::setUp();
        $settingsParameters = $this->getSettingsData();
        $this->settings = new Settings(
            $settingsParameters['name'],
            $settingsParameters['uuid'],
            $settingsParameters['apiKey'],
            $settingsParameters['secretKey'],
            $settingsParameters['url'],
            $settingsParameters['sharedSecret'],
            $settingsParameters['webhook']
        );
    }

    public function tearDown() : void
    {
        parent::tearDown();
        unset($this->settings);
    }

    /**
     * @dataProvider settingsDataProvider
     * @param $settingsData
     */
    public function testToArray($settingsData)
    {
        $this->assertEquals($this->settings->toArray(), $settingsData);
    }

    /**
     * @dataProvider settingsDataProvider
     * @param $settingsData
     */
    public function testGetUuid($settingsData)
    {
        $emptySettings = new Settings('', '', '', '', '');
        $this->assertFalse($emptySettings->getUuid());
        $this->assertEquals($this->settings->getUuid(), $settingsData['uuid']);
    }

    /**
     * @dataProvider settingsDataProvider
     * @param $settingsData
     */
    public function testGetWebhook($settingsData)
    {
        $this->assertEquals($this->settings->getWebhook('http://example1.com/webhooks'), $settingsData['webhook']['http://example1.com/webhooks']);
        $this->assertEquals($this->settings->getWebhook('http://example2.com/webhooks'), $settingsData['webhook']['http://example2.com/webhooks']);
        $this->assertFalse($this->settings->getWebhook('http://non_existing_url'));
    }

    /**
     * @dataProvider settingsDataProvider
     * @param $settingsData
     */
    public function testGetUrl($settingsData)
    {
        $this->assertEquals($this->settings->getUrl(), $settingsData['url']);
    }

    /**
     * @dataProvider settingsDataProvider
     * @param $settingsData
     */
    public function testGetApiKey($settingsData)
    {
        $this->assertEquals($this->settings->getApiKey(), $settingsData['apiKey']);
    }

    /**
     * @dataProvider settingsDataProvider
     * @param $settingsData
     */
    public function testGetSecretKey($settingsData)
    {
        $this->assertEquals($this->settings->getSecretKey(), $settingsData['secretKey']);
    }

    /**
     * @dataProvider settingsDataProvider
     * @param $settingsData
     */
    public function testSharedSecret($settingsData)
    {
        $this->assertEquals($this->settings->getSharedSecret(), $settingsData['sharedSecret']);
    }

    public function getSettingsData()
    {
        return [
            'name' => 'testName',
            'uuid' => '11111111-00000000-00000000-00000000',
            'apiKey' => 'AAAAAA-AAAAAA-AAAAAA',
            'secretKey' => 'BBBBBB-BBBBBB-BBBBBB',
            'url' => 'https://test.url',
            'sharedSecret' => null,
            'webhook' => [
                    'http://example1.com/webhooks' => '00000000-0000-0000-0000-000000000000',
                    'http://example2.com/webhooks' => '11111111-0000-0000-0000-000000000000',
                    ],
        ];
    }

    public function settingsDataProvider()
    {
        return [
            [
                $this->getSettingsData()
            ]
        ];
    }

}