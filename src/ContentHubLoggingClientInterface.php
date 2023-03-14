<?php

namespace Acquia\ContentHubClient;

use Psr\Http\Message\ResponseInterface;

/**
 * Defines the behaviour of an event logger client.
 */
interface ContentHubLoggingClientInterface {

  /**
   * Sends event logs to event microservice.
   *
   * @param array $logs
   *   Array of logs. Example:
   * @code
   * [
   *   'object_id' => 'objectid',
   *   'object_type' => 'objecttype',
   *   'event_name' => 'anevent',
   *   'severity' => 'ERROR',
   *   'content' => 'content',
   *   'origin' => 'auuid',
   * ]
   * @endcode
   *
   * @return array
   *   Response from logging service in an array format.
   *
   * @throws \Exception
   */
  public function sendLogs(array $logs): array;

  /**
   * Pings the service to make sure it is available.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response in array format or null.
   */
  public function ping(): ResponseInterface;

}
