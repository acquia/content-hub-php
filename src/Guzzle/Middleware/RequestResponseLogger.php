<?php

namespace Acquia\ContentHubClient\Guzzle\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RequestResponseLogger.
 *
 * @package Acquia\ContentHubClient\Guzzle\Middleware
 */
class RequestResponseLogger {

  /**
   * Request object instance.
   *
   * @var \Psr\Http\Message\RequestInterface
   */
  protected $request;

  /**
   * Response object instance.
   *
   * @var \Psr\Http\Message\ResponseInterface
   */
  protected $response;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Decoded response body.
   *
   * @var mixed
   */
  protected $decodedResponseBody;

  /**
   * RequestResponseLogger constructor.
   *
   * @param \Psr\Http\Message\RequestInterface $request
   *   Request object instance.
   * @param \Psr\Http\Message\ResponseInterface $response
   *   Response object instance.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   */
  public function __construct(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
    $this->request = $request;
    $this->response = $response;
    $this->logger = $logger;
    $this->decodedResponseBody = json_decode($response->getBody(), TRUE);
  }

  /**
   * Logs response/request data.
   */
  public function log(): void {
    if (!$this->isTrackable()) {
      return;
    }

    $message = $this->buildLogMessage();

    $this->logMessage($message, $this->response->getStatusCode());
  }

  /**
   * Checks if a response can be tracked.
   *
   * @return bool
   *   TRUE in the case when the response should be tracked.
   */
  protected function isTrackable(): bool {
    // Skip tracking for requests without ID.
    if (empty($this->decodedResponseBody['request_id'])) {
      return FALSE;
    }

    // Skip tracking for requests with a shared secret.
    if (isset($this->decodedResponseBody['shared_secret'])) {
      return FALSE;
    }

    // Skip tracking for requests with sensitive data.
    if (isset($this->decodedResponseBody['data']['data']['metadata']['settings'])) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Builds log message.
   *
   * @return string
   *   Log message.
   */
  protected function buildLogMessage(): string {
    return sprintf(
      'Request ID: %s. Method: %s. Path: %s. Status code: %d.',
      $this->decodedResponseBody['request_id'],
      $this->request->getMethod(),
      $this->request->getUri()->getPath(),
      $this->response->getStatusCode()
    );
  }

  /**
   * Logs message depending on response status code.
   *
   * @param string $message
   *   Log message.
   * @param int $responseStatusCode
   *   Response status code.
   */
  protected function logMessage(string $message, int $responseStatusCode): void {
    if ($responseStatusCode >= Response::HTTP_INTERNAL_SERVER_ERROR) {
      $this->logger->error($message);

      return;
    }

    if ($responseStatusCode >= Response::HTTP_BAD_REQUEST) {
      $this->logger->warning($message);

      return;
    }

    $this->logger->info($message);
  }

}
