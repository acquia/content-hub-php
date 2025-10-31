<?php

namespace Acquia\ContentHubClient\Syndication;

/**
 * Represents possible syndication states.
 *
 * @package Acquia\ContentHubClient\Syndication
 */
final class SyndicationState {

  public const PROCESSING = 'processing';
  public const FAILED = 'failed';
  public const QUEUED = 'queued';
  public const PROCESSED = 'processed';
  public const PENDING = 'pending';

}
