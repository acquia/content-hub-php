<?php

namespace Acquia\ContentHubClient\test\Webhook;

use Acquia\ContentHubClient\Webhook\WebhookStatus;
use PHPUnit\Framework\TestCase;

/**
 * Class WebhookStatusTest.
 *
 * @covers \Acquia\ContentHubClient\Webhook\WebhookStatus
 *
 * @package Acquia\ContentHubClient\test\Webhook
 */
class WebhookStatusTest extends TestCase {

  /**
   * Sample webhook status data.
   */
  private const SAMPLE_STATUS_DATA = [
    'uuid' => 'some-uuid',
    'url' => 'https://example.com/webhook',
    'current_state' => 'ACTIVE',
    'status' => 'ENABLED',
    'reason' => 'All good',
    'suppressed_until' => 0,
  ];

  /**
   * WebhookStatus instance.
   *
   * @var \Acquia\ContentHubClient\Webhook\WebhookStatus
   */
  private WebhookStatus $webhookStatus;

  /**
   * {@inheritDoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->webhookStatus = new WebhookStatus(
      self::SAMPLE_STATUS_DATA['uuid'],
      self::SAMPLE_STATUS_DATA['url'],
      self::SAMPLE_STATUS_DATA['current_state'],
      self::SAMPLE_STATUS_DATA['status'],
      self::SAMPLE_STATUS_DATA['reason'],
      self::SAMPLE_STATUS_DATA['suppressed_until']
    );
  }

  /**
   * {@inheritDoc}
   */
  protected function tearDown(): void {
    parent::tearDown();
    unset($this->webhookStatus);
  }

  /**
   * Tests object creation and getters.
   */
  public function testGettersReturnConstructorValues(): void {
    $this->assertEquals(self::SAMPLE_STATUS_DATA['uuid'], $this->webhookStatus->getUuid());
    $this->assertEquals(self::SAMPLE_STATUS_DATA['url'], $this->webhookStatus->getUrl());
    $this->assertEquals(self::SAMPLE_STATUS_DATA['current_state'], $this->webhookStatus->getCurrentState());
    $this->assertEquals(self::SAMPLE_STATUS_DATA['status'], $this->webhookStatus->getStatus());
    $this->assertEquals(self::SAMPLE_STATUS_DATA['reason'], $this->webhookStatus->getReason());
    $this->assertEquals(self::SAMPLE_STATUS_DATA['suppressed_until'], $this->webhookStatus->getSuppressedUntil());
  }

  /**
   * Tests isOf() returns true when status matches.
   */
  public function testIsOfReturnsTrueWhenStatusMatches(): void {
    $this->assertTrue($this->webhookStatus->isOfState(self::SAMPLE_STATUS_DATA['current_state']));
  }

  /**
   * Tests isOf() returns false when status does not match.
   */
  public function testIsOfReturnsFalseWhenStatusDoesNotMatch(): void {
    $this->assertFalse($this->webhookStatus->isOfState('DISABLED'));
  }

  /**
   * Tests isOf() is case sensitive.
   */
  public function testIsOfReturnsFalseWhenStatusDiffersInCase(): void {
    $this->assertFalse($this->webhookStatus->isOfState(strtolower(self::SAMPLE_STATUS_DATA['current_state'])));
  }

  /**
   * Tests isValid() returns true when required fields are set.
   */
  public function testIsValidReturnsTrueWithAllRequiredFields(): void {
    $this->assertTrue($this->webhookStatus->isValid());
  }

  /**
   * Tests isValid() returns false when a required field is empty (uuid).
   */
  public function testIsValidReturnsFalseWhenUuidIsEmpty(): void {
    $status = new WebhookStatus('', self::SAMPLE_STATUS_DATA['url'], self::SAMPLE_STATUS_DATA['current_state'], self::SAMPLE_STATUS_DATA['status'], self::SAMPLE_STATUS_DATA['reason'], 0);
    $this->assertFalse($status->isValid());
  }

  /**
   * Tests isValid() returns false when current_state is empty.
   */
  public function testIsValidReturnsFalseWhenCurrentStateIsEmpty(): void {
    $status = new WebhookStatus(self::SAMPLE_STATUS_DATA['uuid'], self::SAMPLE_STATUS_DATA['url'], '', self::SAMPLE_STATUS_DATA['status'], self::SAMPLE_STATUS_DATA['reason'], 0);
    $this->assertFalse($status->isValid());
  }

  /**
   * Tests isValid() returns false when url is empty.
   */
  public function testIsValidReturnsFalseWhenUrlIsEmpty(): void {
    $status = new WebhookStatus(self::SAMPLE_STATUS_DATA['uuid'], '', self::SAMPLE_STATUS_DATA['current_state'], self::SAMPLE_STATUS_DATA['status'], self::SAMPLE_STATUS_DATA['reason'], 0);
    $this->assertFalse($status->isValid());
  }

  /**
   * Tests isValid() returns false when status is empty.
   */
  public function testIsValidReturnsFalseWhenStatusIsEmpty(): void {
    $status = new WebhookStatus(self::SAMPLE_STATUS_DATA['uuid'], self::SAMPLE_STATUS_DATA['url'], self::SAMPLE_STATUS_DATA['current_state'], '', self::SAMPLE_STATUS_DATA['reason'], 0);
    $this->assertTrue($status->isValid());
  }

  /**
   * Tests isValid() returns true when reason is empty (reason is not required).
   */
  public function testIsValidReturnsTrueWhenReasonIsEmpty(): void {
    $status = new WebhookStatus(self::SAMPLE_STATUS_DATA['uuid'], self::SAMPLE_STATUS_DATA['url'], self::SAMPLE_STATUS_DATA['current_state'], self::SAMPLE_STATUS_DATA['status'], '', 0);
    $this->assertTrue($status->isValid());
    $this->assertEquals('', $status->getReason());
  }

  /**
   * Tests suppression timestamp getter with non-zero value.
   */
  public function testSuppressedUntilGetter(): void {
    $future = time() + 3600;
    $status = new WebhookStatus(self::SAMPLE_STATUS_DATA['uuid'], self::SAMPLE_STATUS_DATA['url'], self::SAMPLE_STATUS_DATA['current_state'], self::SAMPLE_STATUS_DATA['status'], self::SAMPLE_STATUS_DATA['reason'], $future);
    $this->assertEquals($future, $status->getSuppressedUntil());
  }

  /**
   * Tests fromArray() correctly builds object.
   */
  public function testFromArrayCreatesObjectWithValues(): void {
    $status = WebhookStatus::fromArray(self::SAMPLE_STATUS_DATA);
    $this->assertEquals(self::SAMPLE_STATUS_DATA['uuid'], $status->getUuid());
    $this->assertEquals(self::SAMPLE_STATUS_DATA['url'], $status->getUrl());
    $this->assertEquals(self::SAMPLE_STATUS_DATA['current_state'], $status->getCurrentState());
    $this->assertEquals(self::SAMPLE_STATUS_DATA['status'], $status->getStatus());
    $this->assertEquals(self::SAMPLE_STATUS_DATA['reason'], $status->getReason());
    $this->assertEquals(self::SAMPLE_STATUS_DATA['suppressed_until'], $status->getSuppressedUntil());
    $this->assertTrue($status->isValid());
  }

  /**
   * Tests fromArray() with missing keys sets empty defaults and object invalid.
   */
  public function testFromArrayWithMissingKeysProducesInvalidStatus(): void {
    $partial = [
      'uuid' => 'partial-uuid',
      'current_state' => 'ACTIVE',
      'reason' => 'Some reason',
      'suppressed_until' => 0,
    ];
    $status = WebhookStatus::fromArray($partial);
    $this->assertEquals('partial-uuid', $status->getUuid());
    $this->assertEquals('', $status->getUrl());
    $this->assertEquals('ACTIVE', $status->getCurrentState());
    $this->assertEquals('', $status->getStatus());
    $this->assertEquals('Some reason', $status->getReason());
    $this->assertEquals(0, $status->getSuppressedUntil());
    $this->assertFalse($status->isValid());
  }

  /**
   * Tests fromArray() called with completely empty array yields invalid status.
   */
  public function testFromArrayWithEmptyArrayProducesInvalidStatus(): void {
    $status = WebhookStatus::fromArray([]);
    $this->assertEquals('', $status->getUuid());
    $this->assertEquals('', $status->getUrl());
    $this->assertEquals('', $status->getCurrentState());
    $this->assertEquals('', $status->getStatus());
    $this->assertEquals('', $status->getReason());
    $this->assertEquals(0, $status->getSuppressedUntil());
    $this->assertFalse($status->isValid());
  }

  /**
   * Tests fromArray without reason still valid (reason optional).
   */
  public function testFromArrayWithoutReasonStillValid(): void {
    $data = [
      'uuid' => 'uuid-2',
      'url' => 'https://example.com/2',
      'current_state' => 'INACTIVE',
      'status' => 'ENABLED',
      'suppressed_until' => 0,
    ];
    $status = WebhookStatus::fromArray($data);
    $this->assertEquals('uuid-2', $status->getUuid());
    $this->assertEquals('https://example.com/2', $status->getUrl());
    $this->assertEquals('INACTIVE', $status->getCurrentState());
    $this->assertEquals('ENABLED', $status->getStatus());
    $this->assertEquals('', $status->getReason());
    $this->assertEquals(0, $status->getSuppressedUntil());
    $this->assertTrue($status->isValid());
  }

  /**
   * Tests round-trip fromArray -> toArray.
   */
  public function testArrayRoundTrip(): void {
    $status = WebhookStatus::fromArray(self::SAMPLE_STATUS_DATA);
    $this->assertEquals(self::SAMPLE_STATUS_DATA, $status->toArray());
  }

  /**
   * Tests toArray() returns expected structure.
   */
  public function testToArrayReturnsExpectedStructure(): void {
    $this->assertEquals(self::SAMPLE_STATUS_DATA, $this->webhookStatus->toArray());
  }

  /**
   * Tests fromArray casts suppressed_until to int.
   */
  public function testFromArrayCastsSuppressedUntilToInt(): void {
    $data = self::SAMPLE_STATUS_DATA;
    $data['suppressed_until'] = '12345';
    $status = WebhookStatus::fromArray($data);
    $this->assertSame(12345, $status->getSuppressedUntil());
    $this->assertIsInt($status->getSuppressedUntil());
  }

}
