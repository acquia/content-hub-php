<?php

namespace Acquia\ContentHubClient;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Mock Logger created for fetching log messages.
 */
class LoggerMock implements LoggerInterface {

  use LoggerTrait;

  /**
   * Log messages.
   *
   * @var array
   */
  protected $logMessages = [];

  /**
   * {@inheritDoc}
   */
  public function log($level, $message, array $context = []): void {
    $log_data['message'] = strip_tags($message);
    $log_data['context'] = $context;

    $this->logMessages[$level][] = $log_data;

  }

  /**
   * Helper method that can be used for getting log messages.
   *
   * @return array
   *   Log messages.
   */
  public function getLogMessages(): array {
    return $this->logMessages;
  }

  /**
   * Resets log messages.
   */
  public function reset(): void {
    $this->logMessages = [];
  }

}
