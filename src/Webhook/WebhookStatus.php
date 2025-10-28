<?php

namespace Acquia\ContentHubClient\Webhook;

/**
 * Represents a webhook status returned from the webhook status api.
 */
final class WebhookStatus {

  private string $uuid;
  private string $url;
  private string $current_state;
  private string $status;
  private string $reason;

  public function __construct(string $uuid, string $url, string $current_state, string $status, string $reason) {
    $this->uuid = $uuid;
    $this->url = $url;
    $this->current_state = $current_state;
    $this->status = $status;
    $this->reason = $reason;
  }

  public function getUuid(): string {
    return $this->uuid;
  }

  public function getUrl(): string {
    return $this->url;
  }

  public function getCurrentState(): string {
    return $this->current_state;
  }

  public function getStatus(): string {
    return $this->status;
  }

  public function isOf(string $status): bool {
    return $this->status === $status;
  }

  public function getReason(): string {
    return $this->reason;
  }

  /**
   * Create a WebhookStatus instance from an array.
   *
   * @param array $data
   *   Array containing webhook status data with keys: uuid, url, current_state, status, reason.
   *
   * @return self
   */
  public static function fromArray(array $data): self {
    return new self(
      $data['uuid'] ?? '',
      $data['url'] ?? '',
      $data['current_state'] ?? '',
      $data['status'] ?? '',
      $data['reason'] ?? ''
    );
  }

  /**
   * Convert the WebhookStatus instance to an array.
   *
   * @return array
   */
  public function toArray(): array {
    return [
      'uuid' => $this->uuid,
      'url' => $this->url,
      'current_state' => $this->current_state,
      'status' => $this->status,
      'reason' => $this->reason,
    ];
  }

}
