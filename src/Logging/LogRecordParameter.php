<?php

namespace Acquia\ContentHubClient\Logging;

/**
 * Parameter object, represents a log record.
 */
final class LogRecordParameter {

  public string $logLevel;
  private string $requestId;
  private string $method;
  private string $apiCall;
  private int $statusCode;
  private string $reasonPhrase;
  private int $errorCode;
  private string $errorMessage;
  private string $data;

  public function __construct(
    string $log_level,
    string $request_id,
    string $method,
    string $api_call,
    int $status_code,
    string $reason_phrase,
    int $error_code,
    string $error_message,
    string $data
  ) {
    $this->logLevel = $log_level;
    $this->requestId = $request_id;
    $this->method = $method;
    $this->apiCall = $api_call;
    $this->statusCode = $status_code;
    $this->reasonPhrase = $reason_phrase;
    $this->errorCode = $error_code;
    $this->errorMessage = $error_message;
    $this->data = $data;
  }

  /**
   * @return string
   */
  public function getRequestId(): string {
    return $this->requestId;
  }

  /**
   * @return string
   */
  public function getMethod(): string {
    return $this->method;
  }

  /**
   * @return string
   */
  public function getApiCall(): string {
    return $this->apiCall;
  }

  /**
   * @return int
   */
  public function getStatusCode(): int {
    return $this->statusCode;
  }

  /**
   * @return string
   */
  public function getReasonPhrase(): string {
    return $this->reasonPhrase;
  }

  /**
   * @return int
   */
  public function getErrorCode(): int {
    return $this->errorCode;
  }

  /**
   * @return string
   */
  public function getErrorMessage(): string {
    return $this->errorMessage;
  }

  /**
   * @return string
   */
  public function getData(): string {
    return $this->data;
  }

}
