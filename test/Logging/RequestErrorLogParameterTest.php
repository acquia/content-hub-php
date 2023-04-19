<?php

namespace Acquia\ContentHubClient\test\Logging;

use Acquia\ContentHubClient\Logging\RequestErrorLogParameter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LogLevel;

/**
 * @coversDefaultClass \Acquia\ContentHubClient\Logging\RequestErrorLogParameter
 */
class RequestErrorLogParameterTest extends TestCase {

  use ProphecyTrait;

  /**
   * Tests property retrieval.
   */
  public function testGeneralPropertyAccess(): void {
    $log_record = $this->newRequestErrorLogParameter();

    $this->assertEquals(LogLevel::WARNING, $log_record->logLevel);
    $this->assertEquals('arequestid', $log_record->getRequestId());
    $this->assertEquals('GET', $log_record->getMethod());
    $this->assertEquals('entities/1e8dceb7-331d-4bf5-89c0-c2cbaaca203f', $log_record->getApiCall());
    $this->assertEquals(400, $log_record->getStatusCode());
    $this->assertEquals('Not found', $log_record->getReasonPhrase());
    $this->assertEquals(4000, $log_record->getErrorCode());
    $this->assertEquals('entity not found', $log_record->getErrorMessage());
    $this->assertEquals('some extra data', $log_record->getMetadata());
  }

  /**
   * Tests error_code property with null value.
   */
  public function testErrorCodeWithNullValue(): void {
    $log_record = $this->newRequestErrorLogParameter(['error_code' => NULL]);
    $this->assertEquals(NULL, $log_record->getErrorCode());
  }

  /**
   * Returns a new LogRecordParameter object.
   *
   * @param array $overrides
   *   A list of overrides, keyed by the constructor parameter names.
   *
   * @return \Acquia\ContentHubClient\Logging\RequestErrorLogParameter
   *   The instantiated object.
   */
  protected function newRequestErrorLogParameter(array $overrides = []): RequestErrorLogParameter {
    $default = [
      'log_level' => LogLevel::WARNING,
      'request_id' => 'arequestid',
      'method' => 'GET',
      'api_call' => 'entities/1e8dceb7-331d-4bf5-89c0-c2cbaaca203f',
      'status_code' => 400,
      'reason_phrase' => 'Not found',
      'error_code' => 4000,
      'error_message' => 'entity not found',
      'metadata' => 'some extra data',
    ];
    $values = array_replace($default, $overrides);
    return new RequestErrorLogParameter(...array_values($values));
  }

}
