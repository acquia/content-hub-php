<?php

namespace Acquia\ContentHubClient\Webhook;

/**
 * Represents a webhook status returned from the webhook status api.
 */
final class WebhookStatus {

  /**
   * The uuid of the webhook.
   *
   * @var string
   */
  private string $uuid;

  /**
   * The url of the webhook.
   *
   * @var string
   */
  private string $url;

  /**
   * The current state of the webhook.
   *
   * @var string
   */
  private string $currentState;

  /**
   * The status of the webhook.
   *
   * @var string
   */
  private string $status;

  /**
   * The reason for the webhook status.
   *
   * @var string
   */
  private string $reason;

  /**
   * The suppression time for the webhook (Unix timestamp until which the webhook is suppressed).
   *
   * @var int
   */
  private int $suppressedUntil;

  /**
   * Constructs a WebhookStatus object.
   *
   * @param string $uuid
   *   The uuid of the webhook.
   * @param string $url
   *   The url of the webhook.
   * @param string $current_state
   *   The current state of the webhook.
   * @param string $status
   *   The status of the webhook.
   * @param string $reason
   *   The reason for the webhook status.
   * @param int $suppressed_until
   *   The UNIX timestamp until which the webhook is suppressed (0 if not suppressed).
   */
  public function __construct(string $uuid, string $url, string $current_state, string $status, string $reason, int $suppressed_until) {
    $this->uuid = $uuid;
    $this->url = $url;
    $this->currentState = $current_state;
    $this->status = $status;
    $this->reason = $reason;
    $this->suppressedUntil = $suppressed_until;
  }

  /**
   * Returns the uuid of the webhook.
   *
   * @return string
   *   The uuid.
   */
  public function getUuid(): string {
    return $this->uuid;
  }

  /**
   * Returns the url of the webhook.
   *
   * @return string
   *   The url.
   */
  public function getUrl(): string {
    return $this->url;
  }

  /**
   * Returns the current state of the webhook.
   *
   * @return string
   *   The current state.
   */
  public function getCurrentState(): string {
    return $this->currentState;
  }

  /**
   * Returns the status of the webhook.
   *
   * @return string
   *   The status.
   */
  public function getStatus(): string {
    return $this->status;
  }

  /**
   * Checks if the webhook has a specific status.
   *
   * @param string $state
   *   The status to check against.
   *
   * @return bool
   *   TRUE if the webhook status matches, FALSE otherwise.
   */
  public function isOfState(string $state): bool {
    return $this->currentState === $state;
  }

  /**
   * Returns the reason for the webhook status.
   *
   * @return string
   *   The reason.
   */
  public function getReason(): string {
    return $this->reason;
  }

  /**
   * Returns the suppression timestamp for the webhook.
   *
   * @return int
   *   A UNIX timestamp (0 if not suppressed).
   */
  public function getSuppressedUntil(): int {
    return $this->suppressedUntil;
  }

  /**
   * Checks if the WebhookStatus is valid.
   *
   * @return bool
   *   Valid if all the required values are set.
   */
  public function isValid(): bool {
    $required = [
      $this->uuid,
      $this->url,
      $this->currentState,
    ];

    return !in_array('', $required, TRUE);
  }

  /**
   * Create a WebhookStatus instance from an array.
   *
   * @param array $data
   *   Array containing webhook status data with keys: uuid, url, current_state, status, reason, suppressed_until.
   *
   * @return self
   *   An instance of this class.
   */
  public static function fromArray(array $data): self {
    return new self(
      $data['uuid'] ?? '',
      $data['url'] ?? '',
      $data['current_state'] ?? '',
      $data['status'] ?? '',
      $data['reason'] ?? '',
      (int) ($data['suppressed_until'] ?? 0)
    );
  }

  /**
   * Convert the WebhookStatus instance to an array.
   *
   * @return array
   *   The webhook status in array format.
   */
  public function toArray(): array {
    return [
      'uuid' => $this->uuid,
      'url' => $this->url,
      'current_state' => $this->currentState,
      'status' => $this->status,
      'reason' => $this->reason,
      'suppressed_until' => $this->suppressedUntil,
    ];
  }

}
