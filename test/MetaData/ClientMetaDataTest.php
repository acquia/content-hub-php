<?php

namespace Acquia\ContentHubClient\test\MetaData;

use Acquia\ContentHubClient\MetaData\ClientMetaData;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Acquia\ContentHubClient\MetaData\ClientMetaData
 *
 * @package Acquia\ContentHubClient\test
 */
class ClientMetaDataTest extends TestCase {

  /**
   * Test metadata.
   *
   * @var array
   */
  protected array $metadata = [
    'client_type' => 'drupal',
    'is_publisher' => TRUE,
    'is_subscriber' => FALSE,
    'config' => [
      'valid_ssl' => TRUE,
      'drupal_version' => '10.1.1',
      'ch_version' => '3.3.0',
    ],
  ];

  /**
   * SUT.
   *
   * @var \Acquia\ContentHubClient\MetaData\ClientMetaData
   */
  protected ClientMetaData $sut;

  /**
   * Tests getMetadata method.
   *
   * @covers ::getMetadata
   */
  public function testGetMetaData(): void {
    $this->sut = new ClientMetaData($this->metadata['client_type'], $this->metadata['is_publisher'], $this->metadata['is_subscriber'], $this->metadata['config']);
    $client_metadata = $this->sut->getMetadata();
    $this->assertEquals($this->metadata, $client_metadata);
  }

  /**
   * Tests fromArray method.
   *
   * @covers ::fromArray
   */
  public function testMetaDataCreationFromArray(): void {
    $this->sut = ClientMetaData::fromArray($this->metadata);
    $client_metadata = $this->sut->getMetadata();
    $this->assertEquals($this->metadata, $client_metadata);
  }

}
