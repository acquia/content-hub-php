<?php

namespace Acquia\ContentHubClient\test\CDF;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDF\ClientCDFObject;
use Acquia\ContentHubClient\CDFAttribute;
use PHPUnit\Framework\TestCase;

class ClientCDFObjectTest extends TestCase {

  private const METADATA = [
    'origin' => 'some-origin',
    'created' => 'some-time',
    'modified' => 'some-other-time',
    'settings' => [
      'uuid' => 'some-uuid',
      'name' => 'some-name',
      'apiKey' => 'some-api-key',
      'secretKey' => 'some-secret-key',
      'url' => 'some-url',
      'sharedSecret' => NULL,
      'webhook' => [
        'webhook1' => 'w1-uuid',
        'webhook2' => 'w2-uuid',
      ],
    ],
  ];

  /**
   * @var ClientCDFObject
   */
  private $clientCdfObject;

  public function setUp(): void {
    parent::setUp();

    $this->clientCdfObject = ClientCDFObject::create('client_cdf_id_1', self::METADATA);
  }

  public function tearDown(): void {
    parent::tearDown();

    unset($this->clientCdfObject);
  }

  public function testGetClientName(): void {
    $clientName = $this->clientCdfObject->getClientName();

    $this->assertInstanceOf(CDFAttribute::class, $clientName);
    $this->assertEquals(CDFAttribute::TYPE_STRING, $clientName->getType());
    $this->assertEquals(self::METADATA['settings']['name'], $clientName->getValue()[CDFObject::LANGUAGE_UNDETERMINED]);
  }

  public function testGetSettings(): void {
    $this->assertEquals(self::METADATA['settings'], $this->clientCdfObject->getSettings()->toArray());
  }

  public function testGetWebhookReturnsNonEmptyArrayWhenThereAreSome(): void {
    $this->assertEquals(self::METADATA['settings']['webhook'], $this->clientCdfObject->getWebhook());
  }

  public function testGetWebhookReturnsEmptyArrayWhenThereIsNone(): void {
    $metadata = self::METADATA;
    unset($metadata['settings']['webhook']);
    $this->clientCdfObject = ClientCDFObject::create('client_cdf_id_1', $metadata);

    $this->assertCount(0, $this->clientCdfObject->getWebhook());
  }

}
