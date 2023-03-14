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
   *   Array of logs. Format:
   *
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
