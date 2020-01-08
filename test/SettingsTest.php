<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\Settings;
use PHPUnit\Framework\TestCase;

/**
 * Class SettingsTest.
 *
 * @covers \Acquia\ContentHubClient\Settings
 *
 * @package Acquia\ContentHubClient\test
 */
class SettingsTest extends TestCase {

  /**
   * Settings instance.
   *
   * @var \Acquia\ContentHubClient\Settings
   */
  private $settings;

  /**
   * {@inheritDoc}
   */
  public function setUp(): void {
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

  /**
   * {@inheritDoc}
   */
  public function tearDown(): void {
    parent::tearDown();
    unset($this->settings);
  }

  /**
   * @covers \Acquia\ContentHubClient\Settings::toArray
   *
   * @dataProvider settingsDataProvider
   *
   * @param array $settingsData
   *   Settings data.
   */
  public function testToArray(array $settingsData) {
    $this->assertEquals($this->settings->toArray(), $settingsData);
  }

  /**
   * @covers \Acquia\ContentHubClient\Settings::getUuid
   *
   * @dataProvider settingsDataProvider
   *
   * @param array $settingsData
   *   Settings data.
   */
  public function testGetUuid(array $settingsData) {
    $emptySettings = new Settings('', '', '', '', '');
    $this->assertFalse($emptySettings->getUuid());
    $this->assertEquals($this->settings->getUuid(), $settingsData['uuid']);
  }

  /**
   * @covers \Acquia\ContentHubClient\Settings::getWebhook
   *
   * @dataProvider settingsDataProvider
   *
   * @param array $settingsData
   *   Settings data.
   */
  public function testGetWebhook(array $settingsData) {
    $this->assertEquals(
      $this->settings->getWebhook('http://example1.com/webhooks'),
      $settingsData['webhook']['http://example1.com/webhooks']
    );
    $this->assertEquals(
      $this->settings->getWebhook('http://example2.com/webhooks'),
      $settingsData['webhook']['http://example2.com/webhooks']
    );
    $this->assertFalse($this->settings->getWebhook('http://non_existing_url'));
  }

  /**
   * @covers \Acquia\ContentHubClient\Settings::getUrl
   *
   * @dataProvider settingsDataProvider
   *
   * @param array $settingsData
   *   Settings data.
   */
  public function testGetUrl(array $settingsData) {
    $this->assertEquals($this->settings->getUrl(), $settingsData['url']);
  }

  /**
   * @covers \Acquia\ContentHubClient\Settings::getApiKey
   *
   * @dataProvider settingsDataProvider
   *
   * @param array $settingsData
   *   Settings data.
   */
  public function testGetApiKey(array $settingsData) {
    $this->assertEquals($this->settings->getApiKey(), $settingsData['apiKey']);
  }

  /**
   * @covers \Acquia\ContentHubClient\Settings::getSecretKey
   *
   * @dataProvider settingsDataProvider
   *
   * @param array $settingsData
   *   Settings data.
   */
  public function testGetSecretKey(array $settingsData) {
    $this->assertEquals($this->settings->getSecretKey(),
      $settingsData['secretKey']);
  }

  /**
   * @covers \Acquia\ContentHubClient\Settings::getSharedSecret
   *
   * @dataProvider settingsDataProvider
   *
   * @param array $settingsData
   *   Settings data.
   */
  public function testSharedSecret(array $settingsData) {
    $this->assertEquals($this->settings->getSharedSecret(),
      $settingsData['sharedSecret']);
  }

  /**
   * Returns settings data.
   *
   * @return array
   *   Test data.
   */
  public function getSettingsData() {
    return [
      'name' => 'testName',
      'uuid' => '11111111-00000000-00000000-00000000',
      'apiKey' => 'AAAAAA-AAAAAA-AAAAAA',
      'secretKey' => 'BBBBBB-BBBBBB-BBBBBB',
      'url' => 'https://test.url',
      'sharedSecret' => NULL,
      'webhook' => [
        'http://example1.com/webhooks' => '00000000-0000-0000-0000-000000000000',
        'http://example2.com/webhooks' => '11111111-0000-0000-0000-000000000000',
      ],
    ];
  }

  /**
   * Settings data provider.
   *
   * @return array
   *   Test data.
   */
  public function settingsDataProvider() {
    return [
      [
        $this->getSettingsData(),
      ],
    ];
  }

}
