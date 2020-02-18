<?php

namespace Acquia\ContentHubClient\test;

use PHPUnit\Framework\TestCase;

use Acquia\ContentHubClient\Webhook;

/**
 * Class WebhookTest.
 *
 * @covers \Acquia\ContentHubClient\Webhook
 *
 * @package Acquia\ContentHubClient\test
 */
class WebhookTest extends TestCase {

  /**
   * Webhook instance.
   *
   * @var \Acquia\ContentHubClient\Webhook
   */
  private $webhook;

  /**
   * Webhook definition.
   *
   * @var array
   */
  private $definition;

  /**
   * {@inheritDoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->definition = [
      'uuid' => 'some-uuid',
      'client_uuid' => 'some-client_uuid',
      'client_name' => 'some-client-name',
      'url' => 'some-url',
      'version' => 1,
      'disable_retries' => FALSE,
      'filters' => [
        'filter1_uuid',
        'filter2_uuid',
      ],
      'status' => 'some-status',
      'is_migrated' => FALSE,
      'suppressed_until' => 0,
    ];

    $this->webhook = new Webhook($this->definition);
  }

  /**
   * {@inheritDoc}
   */
  protected function tearDown() {
    parent::tearDown();

    unset($this->webhook, $this->definition);
  }

  /**
   * Tests webhook creation.
   */
  public function testGettersReturnWhatPropertiesWereSetTo(): void {
    $this->assertEquals(
      [
        $this->webhook->getUuid(),
        $this->webhook->getClientUuid(),
        $this->webhook->getClientName(),
        $this->webhook->getUrl(),
        $this->webhook->getVersion(),
        $this->webhook->getDisableRetries(),
        $this->webhook->getFilters(),
        $this->webhook->getStatus(),
        $this->webhook->getIsMigrated(),
        $this->webhook->getSuppressedUntil(),
      ],
      array_values($this->definition)
    );

    $this->assertEquals($this->webhook->getDefinition(), $this->definition);
  }

  /**
   * Tests webhook status.
   */
  public function testGetIsEnabledReturnsTrueIfStatusIsEnabled(): void {
    $this->definition['status'] = 'ENABLED';
    $this->webhook = new Webhook($this->definition);
    $this->assertTrue($this->webhook->isEnabled());
  }

  /**
   * Tests webhook status.
   */
  public function testGetIsEnabledReturnsTrueIfStatusIsEmptyString(): void {
    $this->definition['status'] = '';
    $this->webhook = new Webhook($this->definition);
    $this->assertTrue($this->webhook->isEnabled());
  }

  /**
   * Tests webhook status.
   */
  public function testGetIsEnabledReturnsFalseIfStatusIsNeitherENABLEDnorEmptyString(): void { // phpcs:ignore
    $this->definition['status'] = 'some-status';
    $this->webhook = new Webhook($this->definition);
    $this->assertFalse($this->webhook->isEnabled());
  }

}
