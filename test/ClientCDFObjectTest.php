<?php

namespace Acquia\ContentHubClient\test;


use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDF\ClientCDFObject;
use Acquia\ContentHubClient\CDFAttribute;
use PHPUnit\Framework\TestCase;

class ClientCDFObjectTest extends TestCase
{
  /**
   * @var ClientCDFObject
   */
  private $clientCdfObject;

  /**
   *
   */
  public function setUp() : void
  {
    parent::setUp();
    $settingsData = $this->getSettingsData();
    try {
      $this->clientCdfObject = ClientCDFObject::create(
        'client_cdf_id_1',
        $settingsData
      );
    } catch (\Exception $exception) {
    }
  }

  /**
   *
   */
  public function tearDown() :void
  {
    parent::tearDown();
    unset($this->clientCdfObject);
  }

  /**
   *
   */
  public function testGetClientName() : void
  {
    $this->assertInstanceOf(CDFAttribute::class, $this->clientCdfObject->getClientName());
    $this->assertEquals(CDFAttribute::TYPE_STRING, $this->clientCdfObject->getClientName()->getType());
    $this->assertEquals($this->getSettingsData()['settings']['name'], $this->clientCdfObject->getClientName()->getValue()[CDFObject::LANGUAGE_UNDETERMINED]);
  }

  /**
   *
   */
  public function testGetSettings() : void
  {
    $this->assertEquals($this->getSettingsData()['settings'], $this->clientCdfObject->getSettings()->toArray());
  }

  /**
   *
   */
  public function testGetWebhook() : void
  {
    $this->assertEquals($this->getSettingsData()['settings']['webhook'], $this->clientCdfObject->getWebhook());
  }

  public function getSettingsData() : array
  {
    return [

      'origin' => '22222222-00000000-00000000-00000000',
      'created' => '2014-12-21T20:12:11+00:00Z',
      'modified' => '2015-12-21T20:12:11+00:00Z',

      'settings' => [
        'uuid' => '11111111-00000000-00000000-00000000',
        'name' => 'test_client_name_1',
        'apiKey' => 'AAAAAA-AAAAAA-AAAAAA',
        'secretKey' => 'BBBBBB-BBBBBB-BBBBBB',
        'url' => 'https://test.url',
        'sharedSecret' => null,
        'webhook' => [
          'http://example1.com/webhooks' => '00000000-0000-0000-0000-000000000000',
          'http://example2.com/webhooks' => '11111111-0000-0000-0000-000000000000',
        ],
      ],
    ];
  }

}