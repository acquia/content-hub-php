<?php

namespace Acquia\ContentHubClient\Webhook;

/**
 * Records the possible webhook statuses.
 */
final class WebhookStatusState {

  public const UNREACHABLE = 'unreachable';
  public const UNREGISTERED = 'unregistered';
  public const SUPPRESSED = 'suppressed';
  public const DISABLED = 'disabled';
  public const HEALTHY = 'healthy';
  public const UNKNOWN = 'unknown';

}
