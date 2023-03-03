<?php

namespace Acquia\ContentHubClient;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Mock Logger created for fetching log messages.
 */
class LoggerMock implements LoggerInterface {

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

  /**
   * {@inheritDoc}
   */
  public function emergency($message, array $context = []) {
  }

  /**
   * {@inheritDoc}
   */
  public function alert($message, array $context = []) {
  }

  /**
   * {@inheritDoc}
   */
  public function critical($message, array $context = []) {
  }

  /**
   * {@inheritDoc}
   */
  public function error($message, array $context = []) {
  }

  /**
   * {@inheritDoc}
   */
  public function warning($message, array $context = []) {
  }

  /**
   * {@inheritDoc}
   */
  public function notice($message, array $context = []) {
  }

  /**
   * {@inheritDoc}
   */
  public function info($message, array $context = []) {
  }

  /**
   * {@inheritDoc}
   */
  public function debug($message, array $context = []) {
  }

}
