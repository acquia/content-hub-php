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
  public function log($level, string|\Stringable $message, array $context = []): void {
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
  public function emergency(string|\Stringable $message, array $context = []): void {
  }

  /**
   * {@inheritDoc}
   */
  public function alert(string|\Stringable $message, array $context = []): void {
  }

  /**
   * {@inheritDoc}
   */
  public function critical(string|\Stringable $message, array $context = []): void {
  }

  /**
   * {@inheritDoc}
   */
  public function error(string|\Stringable $message, array $context = []): void {
  }

  /**
   * {@inheritDoc}
   */
  public function warning(string|\Stringable $message, array $context = []): void {
  }

  /**
   * {@inheritDoc}
   */
  public function notice(string|\Stringable $message, array $context = []): void {
  }

  /**
   * {@inheritDoc}
   */
  public function info(string|\Stringable $message, array $context = []): void {
  }

  /**
   * {@inheritDoc}
   */
  public function debug(string|\Stringable $message, array $context = []): void {
  }

}
