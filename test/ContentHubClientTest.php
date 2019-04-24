<?php

namespace Acquia\ContentHubClient\test;


use Acquia\ContentHubClient\ContentHubClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class ContentHubClientTest extends TestCase
{
  /**
   * @var ContentHubClient
   */
  private $contentHubClient;

  /**
   *
   */
  public function setUp() : void
  {
    parent::setUp();

    $this->contentHubClient = \Mockery::mock(ContentHubClient::class)
      ->makePartial();
  }

  /**
   *
   */
  public function tearDown() : void
  {
    parent::tearDown();
    unset($this->contentHubClient);
    \Mockery::close();
    gc_collect_cycles();
  }

  public function testDefinition()
  {
    $successJsonResponse = \GuzzleHttp\json_encode([
      'response_code' => 200,
    ]);
    $failureJsonResponse = \GuzzleHttp\json_encode([
      'response_code' => 404,
    ]);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturn($successJsonResponse);
    $responseMock = \Mockery::mock(Response::class);
    $this->contentHubClient->shouldReceive('request')
      ->andReturn($responseMock);

    try {
      $this->assertEquals($successJsonResponse, $this->contentHubClient->definition('dummyendpoint'));
      $this->assertNotEquals($failureJsonResponse, $this->contentHubClient->definition('dummyendpoint'));
    } catch (GuzzleException $exception) {
    }
  }

  public function testPutEntity()
  {
    $successJsonResponse = \GuzzleHttp\json_encode([
      'response_code' => 200,
    ]);
    $failureJsonResponse = \GuzzleHttp\json_encode([
      'response_code' => 404,
    ]);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturn($successJsonResponse);
    $responseMock = \Mockery::mock(Response::class);
    $this->contentHubClient->shouldReceive('request')
      ->andReturn($responseMock);

    try {
      $this->assertEquals($successJsonResponse, $this->contentHubClient->definition('dummyendpoint'));
      $this->assertNotEquals($failureJsonResponse, $this->contentHubClient->definition('dummyendpoint'));
    } catch (GuzzleException $exception) {
    }
  }

}
