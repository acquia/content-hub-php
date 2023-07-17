<?php

namespace Acquia\ContentHubClient\MetaData;

/**
 * Client metadata.
 */
class ClientMetaData {

  /**
   * Client type. e.g. drupal, pwa_app, nextjs_app etc.
   *
   * @var string
   */
  protected string $clientType;

  /**
   * Whether this site is a publiher or not.
   *
   * @var bool
   */
  protected bool $isPublisher;

  /**
   * Whether this site is a subscriber or not.
   *
   * @var bool
   */
  protected bool $isSubscriber;

  /**
   * Additional config metadata.
   *
   * E.g. drupal_version, ch module version, valid ssl etc.
   *
   * @var array
   */
  protected array $clientConfig;

  /**
   * ClientMetadata constructor.
   *
   * @param string $client_type
   *   Client type. e.g. drupal, pwa_app, nextjs_app etc.
   * @param bool $is_publisher
   *   Whether this site is a publiher or not.
   * @param bool $is_subscriber
   *   Whether this site is a subscriber or not.
   * @param array $client_config
   *   Additional config metadata.
   */
  public function __construct(string $client_type, bool $is_publisher, bool $is_subscriber, array $client_config = []) {
    $this->clientType = $client_type;
    $this->isPublisher = $is_publisher;
    $this->isSubscriber = $is_subscriber;
    $this->clientConfig = $client_config;
  }

  /**
   * Constructs a new object from metadata array.
   *
   * @param array $metadata
   *   Metadata array.
   *
   * @return \Acquia\ContentHubClient\MetaData\ClientMetaData
   *   ClientMetaData object.
   */
  public static function fromArray(array $metadata): ClientMetadata {
    if ($metadata === []) {
      $metadata['client_type'] = '';
      $metadata['is_publisher'] = FALSE;
      $metadata['is_subscriber'] = FALSE;
      $metadata['config'] = [];
    }
    if (isset($metadata['client_type'], $metadata['is_publisher'], $metadata['is_subscriber'])) {
      return new static($metadata['client_type'], $metadata['is_publisher'], $metadata['is_subscriber'], $metadata['config'] ?? []);
    }
    throw new \RuntimeException('All the attributes: "client_type", "is_publisher", "is_subscriber" are required.');
  }

  /**
   * Returns metadata of the client in array.
   *
   * @return array
   *   Client metadata array.
   */
  public function toArray(): array {
    return [
      'client_type' => $this->clientType,
      'is_publisher' => $this->isPublisher,
      'is_subscriber' => $this->isSubscriber,
      'config' => $this->clientConfig,
    ];
  }

}
