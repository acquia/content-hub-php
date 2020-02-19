<?php

namespace Acquia\ContentHubClient;

/**
 * Class Webhook.
 *
 * Webhook representation.
 *
 * @package Acquia\ContentHubClient
 */
class Webhook {

  /**
   * Webhook definition.
   *
   * @var array
   */
  protected $definition;

  /**
   * Webhook constructor.
   *
   * @param array $definition
   *   Webhook definition.
   */
  public function __construct(array $definition) {
    $this->definition = $definition;
  }

  /**
   * Returns webhook UUID.
   *
   * @return string
   *   Webhook UUID.
   */
  public function getUuid() {
    return $this->definition['uuid'];
  }

  /**
   * Returns client UUID of the webhook.
   *
   * @return string
   *   Client's UUID.
   */
  public function getClientUuid() {
    return $this->definition['client_uuid'];
  }

  /**
   * Returns client name of the webhook.
   *
   * @return string
   *   Client's name.
   */
  public function getClientName() {
    return $this->definition['client_name'];
  }

  /**
   * Returns URL of the webhook.
   *
   * @return string
   *   Webhook URL.
   */
  public function getUrl() {
    return $this->definition['url'];
  }

  /**
   * Returns version of the webhook.
   *
   * @return string
   *   Webhook version string.
   */
  public function getVersion() {
    return $this->definition['version'];
  }

  /**
   * Returns state of the 'disable_retries' option.
   *
   * @return string
   *   State of the 'disable_retries' option.
   */
  public function getDisableRetries() {
    return $this->definition['disable_retries'];
  }

  /**
   * Returns filters list of the webhook.
   *
   * @return array
   *   Filters list.
   */
  public function getFilters() {
    return $this->definition['filters'];
  }

  /**
   * Returns status of the webhook.
   *
   * @return string
   *   Webhook status.
   */
  public function getStatus() {
    return $this->definition['status'];
  }

  /**
   * Returns 'is_migrated' property.
   *
   * @return bool
   *   'is_migrated' property.
   */
  public function getIsMigrated(): bool {
    return $this->definition['is_migrated'];
  }

  /**
   * Returns 'suppressed_until' property.
   *
   * @return int
   *   'suppressed_until' property.
   */
  public function getSuppressedUntil(): int {
    return $this->definition['suppressed_until'];
  }

  /**
   * Returns definition of the webhook.
   *
   * @return array
   *   Webhook definition.
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Returns the state of the webhook.
   *
   * @return bool
   *   Whether the webhook is in enabled or in the disabled state.
   */
  public function isEnabled() {
    $enabled = [
      'ENABLED',
      '',
    ];
    return in_array($this->getStatus(), $enabled, TRUE);
  }

}
