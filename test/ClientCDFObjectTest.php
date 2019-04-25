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
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function tearDown() : void
  {
    parent::tearDown();
    unset($this->clientCdfObject);
  }

  /**
   * {@inheritdoc}
   */
  public function testGetClientName() : void
  {
    $clientName = $this->clientCdfObject->getClientName();

    $this->assertInstanceOf(CDFAttribute::class, $clientName);
    $this->assertEquals(CDFAttribute::TYPE_STRING, $clientName->getType());
    $this->assertEquals($this->getSettingsData()['settings']['name'], $clientName->getValue()[CDFObject::LANGUAGE_UNDETERMINED]);
  }

  /**
   * {@inheritdoc}
   */
  public function testGetSettings() : void
  {
    $this->assertEquals($this->getSettingsData()['settings'], $this->clientCdfObject->getSettings()->toArray());
  }

  /**
   * {@inheritdoc}
   */
  public function testGetWebhook() : void
  {
    $this->assertEquals($this->getSettingsData()['settings']['webhook'], $this->clientCdfObject->getWebhook());
  }

  /**
   * @return array
   */
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