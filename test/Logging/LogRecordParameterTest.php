<?php

namespace Acquia\ContentHubClient\test\Logging;

use Acquia\ContentHubClient\Logging\LogRecordParameter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LogLevel;

/**
 * @coversDefaultClass \Acquia\ContentHubClient\Logging\LogRecordParameter;
 */
class LogRecordParameterTest extends TestCase {

  use ProphecyTrait;

  /**
   * Tests property retrieval.
   */
  public function testPropertyAccess(): void {
    $log_record = new LogRecordParameter(
      LogLevel::WARNING,
      'arequestid',
      'GET',
      'entities/1e8dceb7-331d-4bf5-89c0-c2cbaaca203f',
      400,
      'Not found',
      4000,
      'entity not found',
      'some extra data',
    );

    $this->assertEquals(LogLevel::WARNING, $log_record->logLevel);
    $this->assertEquals('arequestid', $log_record->getRequestId());
    $this->assertEquals('GET', $log_record->getMethod());
    $this->assertEquals('entities/1e8dceb7-331d-4bf5-89c0-c2cbaaca203f', $log_record->getApiCall());
    $this->assertEquals(400, $log_record->getStatusCode());
    $this->assertEquals('Not found', $log_record->getReasonPhrase());
    $this->assertEquals(4000, $log_record->getErrorCode());
    $this->assertEquals('entity not found', $log_record->getErrorMessage());
    $this->assertEquals('some extra data', $log_record->getData());
  }

}
