<?php

namespace Acquia\ContentHubClient\test;


use Acquia\ContentHubClient\CDF\CDFObject;
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
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturn($jsonResponses[200]);
    $responseMock = \Mockery::mock(Response::class);
    $this->contentHubClient->shouldReceive('request')
      ->andReturn($responseMock);

    try {
      $this->assertEquals($jsonResponses[200], $this->contentHubClient->definition('dummyendpoint'));
      $this->assertNotEquals($jsonResponses[404], $this->contentHubClient->definition('dummyendpoint'));
    } catch (GuzzleException $exception) {
    }
  }

  public function testPutEntity()
  {
    $cdfObjectMock = $this->getCdfObjectMock();

    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('put')
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    try {
      $this->assertEquals($jsonResponses[200], $this->contentHubClient->putEntities($cdfObjectMock));
      $this->assertEquals($jsonResponses[404], $this->contentHubClient->putEntities($cdfObjectMock));
    } catch (GuzzleException $exception) {
    }
  }

  public function getJsonResponses()
  {
    $successJsonResponse = \GuzzleHttp\json_encode([
      'response_code' => 200,
    ]);
    $failureJsonResponse = \GuzzleHttp\json_encode([
      'response_code' => 404,
    ]);

    return [
      200 => $successJsonResponse,
      404 => $failureJsonResponse,
    ];
  }

  public function testPutEntities()
  {
    $cdfObjectMock = $this->getCdfObjectMock();

    $successJsonResponse = \GuzzleHttp\json_encode([
      'response_code' => 200,
    ]);
    $failureJsonResponse = \GuzzleHttp\json_encode([
      'response_code' => 404,
    ]);
    $this->contentHubClient->shouldReceive('put')
      ->andReturnValues([$successJsonResponse, $failureJsonResponse]);

    try {
      $this->assertEquals($successJsonResponse, $this->contentHubClient->putEntity($cdfObjectMock));
      $this->assertEquals($failureJsonResponse, $this->contentHubClient->putEntity($cdfObjectMock));
    } catch (GuzzleException $exception) {
    }
  }

  public function getCdfObjectMock()
  {
    $cdfObjectMock = $this->getMockBuilder(CDFObject::class)
      ->disableOriginalConstructor()
      ->setMethods(['toArray', 'getUuid'])
      ->getMock();

    $cdfObjectMock->expects($this->any())
      ->method('toArray')
      ->willReturn([]);

    $cdfObjectMock->expects($this->any())
      ->method('getUuid')
      ->willReturn('11111111-0000-0000-0000-000000000000');

    return $cdfObjectMock;
  }

}
