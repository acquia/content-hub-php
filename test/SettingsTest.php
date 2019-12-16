<?php

namespace Acquia\ContentHubClient\test;


use Acquia\ContentHubClient\Settings;
use PHPUnit\Framework\TestCase;

class SettingsTest extends TestCase {

  /**
   * @var Settings
   */
  private $settings;

  /**
   * @var array
   */
  private $setting_data;

  /**
   *
   */
  public function setUp(): void {
    parent::setUp();

    $this->setting_data = [
      'name' => 'some-name',
      'uuid' => 'some-uuid',
      'apiKey' => 'some-api-key',
      'secretKey' => 'some-secret-key',
      'url' => 'some-url',
      'sharedSecret' => 'some-shared-secret',
      'webhook' => [
        'webhook1' => 'w1-uuid',
        'webhook2' => 'w2-uuid',
      ],
    ];

    $this->settings = new Settings(...array_values($this->setting_data));
  }

  public function tearDown(): void {
    parent::tearDown();

    unset($this->settings, $this->setting_data);
  }

  public function testToArrayReturnsExactlyTheArraySettingWasCreatedOff(): void {
    $this->assertEquals($this->settings->toArray(), $this->setting_data);
  }

  public function testToGetUuidReturnsFalseIfInitializedWithEmptyValue(): void {
    $this->setting_data['uuid'] = '';
    $this->settings = new Settings(...array_values($this->setting_data));
    $this->assertFalse($this->settings->getUuid());
  }

  public function testToGetWebhookReturnsFalseIfInitializedWithEmptyValue(): void {
    $this->setting_data['webhook'] = [];
    $this->settings = new Settings(...array_values($this->setting_data));
    $this->assertFalse($this->settings->getWebhook());
  }

  public function testToGetWebhookReturnsFalseIfCalledWithNonExistentKey(): void {
    $this->assertFalse($this->settings->getWebhook('some-non-existent-key'));
  }

  public function testToGetWebhookReturnsRespectiveValuefCalledWithExistentKey(): void {
    $webhook = $this->settings->getWebhook('webhook1');

    $this->assertNotFalse($webhook);
    $this->assertEquals('w1-uuid', $webhook);
  }

}