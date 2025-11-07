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

  /**
   * Validates syndication state.
   *
   * @param string $state
   *   The state to validate.
   *
   * @return bool
   *   TRUE if state is valid, FALSE otherwise.
   */
  public function isValidState(string $state): bool {
    $states = $this->getAllSydicationStates();
    if (!in_array($state, $states, TRUE)) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Returns syndication states.
   *
   * @return array
   *   Returns array of valid syndication states.
   */
  public function getAllSydicationStates(): array {
    return [
      self::PROCESSING,
      self::FAILED,
      self::QUEUED,
      self::PROCESSED,
      self::PENDING,
    ];
  }

}
