<?php

namespace Acquia\ContentHubClient\Logging;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Helper trait for logging operations.
 */
trait LoggingHelperTrait {

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  public LoggerInterface $logger;

  /**
   * Obtains the appropriate exception Response, logging error messages according to API call.
   *
   * @param string $method
   *   The Request to Plexus, as defined in the content-hub-php library.
   * @param string $api_call
   *   The api endpoint.
   * @param RequestException $exception
   *   The Exception object.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response after raising an exception.
   *
   *  @codeCoverageIgnore
   */
  protected function getExceptionResponse(string $method, string $api_call, RequestException $exception): ResponseInterface {
    $response = method_exists($exception, 'getResponse') ? $exception->getResponse() : NULL;
    if (!$response) {
      $response = $this->getErrorResponse($exception->getCode(), $exception->getMessage());
    }
    $response_body = json_decode($response->getBody(), TRUE);
    $error_code = $response_body['error']['code'] ?? NULL;
    $error_message = $response_body['error']['message'] ?? $exception->getMessage();

    $log_record = new RequestErrorLogParameter(
      LogLevel::ERROR,
      $response_body['request_id'] ?? '',
      strtoupper($method),
      $api_call,
      $response->getStatusCode(),
      $response->getReasonPhrase(),
      $error_code,
      $error_message,
      print_r($response_body['error']['data'] ?? $response_body['error'] ?? '', TRUE),
    );

    // Customize Error messages according to API Call.
    switch ($api_call) {
      case'settings/webhooks':
        $log_record->logLevel = LogLevel::WARNING;
        break;

      case (preg_match('/filters\?name=*/', $api_call) ? TRUE : FALSE):
      case (preg_match('/settings\/clients\/*/', $api_call) ? TRUE : FALSE):
      case (preg_match('/settings\/webhooks\/.*\/filters/', $api_call) ? TRUE : FALSE):
        $log_record->logLevel = LogLevel::NOTICE;
        break;
    }

    $reason = $this->getResponseReason($log_record);
    $this->logger->log($log_record->logLevel, $reason);

    // Return the response.
    return $response;
  }

  /**
   * Returns a response reason based on the handler.
   *
   * @param \Acquia\ContentHubClient\Logging\RequestErrorLogParameter $record
   *   The log record parameter object.
   *
   * @return string
   *   The formatted log record.
   */
  protected function getResponseReason(RequestErrorLogParameter $record): string {
    switch ($record) {
      case $record->getStatusCode() === 404:
        return $this->get404ResponseReason($record);

      default:
        return $this->getDefaultResponseReason($record);
    }
  }

  /**
   * Returns a formatted response reason based on the 404 status code.
   *
   * @return string
   *   The reason.
   */
  protected function get404ResponseReason(RequestErrorLogParameter $record): string {
    $record->logLevel = LogLevel::WARNING;
    return sprintf('Resource not found in Content Hub: %s.',
      $record->getApiCall(),
    );
  }

  /**
   * Returns a default reason.
   *
   * @param \Acquia\ContentHubClient\Logging\RequestErrorLogParameter $record
   *   The log record parameter object.
   *
   * @return string
   *   The formatted reason.
   */
  protected function getDefaultResponseReason(RequestErrorLogParameter $record): string {
    return sprintf('Request ID: %s, Method: %s, Path: "%s", Status Code: %s, Reason: %s, Error Code: %s, Error Message: "%s". Error data: "%s"',
      $record->getRequestId(),
      $record->getMethod(),
      $record->getApiCall(),
      $record->getStatusCode(),
      $record->getReasonPhrase(),
      $record->getErrorCode(),
      $record->getErrorMessage(),
      $record->getMetadata(),
    );
  }

  /**
   * Returns error response.
   *
   * @param int $code
   *   Status code.
   * @param string $reason
   *   Reason.
   * @param string|null $request_id
   *   The request id from the ContentHub service if available.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   */
  protected function getErrorResponse(int $code, string $reason, ?string $request_id = NULL): ResponseInterface {
    if ($code < 100 || $code >= 600) {
      $code = 500;
    }
    $body = [
      'request_id' => $request_id,
      'error' => [
        'code' => $code,
        'message' => $reason,
      ],
    ];
    return new Response($code, [], json_encode($body), '1.1', $reason);
  }

}
