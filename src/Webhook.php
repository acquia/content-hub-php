<?php


namespace Acquia\ContentHubClient;


class Webhook {

  /**
   * @var array
   */
  protected $definition;

  /**
   * Webhook constructor.
   *
   * @param array $definition
   */
  public function __construct(array $definition) {
    $this->definition = $definition;
  }

  /**
   * @return string
   */
  public function getUuid() {
    return $this->definition['uuid'];
  }

  /**
   * @return string
   */
  public function getClientUuid() {
    return $this->definition['client_uuid'];
  }

  /**
   * @return string
   */
  public function getClientName() {
    return $this->definition['client_name'];
  }

  /**
   * @return string
   */
  public function getUrl() {
    return $this->definition['url'];
  }

  /**
   * @return string
   */
  public function getVersion() {
    return $this->definition['version'];
  }

  /**
   * @return string
   */
  public function getDisableRetries() {
    return $this->definition['disable_retries'];
  }

  /**
   * @return array
   */
  public function getFilters() {
    return $this->definition['filters'];
  }

  /**
   * @return string
   */
  public function getStatus() {
    return $this->definition['status'];
  }

  /**
   * @return array
   */
  public function getDefinition() {
    return $this->definition;
  }

  /**
   * Returns the state of the webhook.
   *
   * @param array $webhook
   *   The webhook definition.
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
