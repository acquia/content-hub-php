<?php

namespace Acquia\ContentHubClient\test;


use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\ContentHubClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ContentHubClientTest extends TestCase
{
  /**
   * @var ContentHubClient ContentHubClient Mock Object
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

  public function testPutEntities()
  {
    $cdfObjectMock = $this->getCdfObjectMock();
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('put')
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    try {
      $this->assertEquals($jsonResponses[200], $this->contentHubClient->putEntity($cdfObjectMock));
      $this->assertEquals($jsonResponses[404], $this->contentHubClient->putEntity($cdfObjectMock));
    } catch (GuzzleException $exception) {
    }
  }

  public function testPostEntities()
  {
    $cdfObjectMock = $this->getCdfObjectMock();
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('post')
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    try {
      $this->assertEquals($jsonResponses[200], $this->contentHubClient->postEntities($cdfObjectMock));
      $this->assertEquals($jsonResponses[404], $this->contentHubClient->postEntities($cdfObjectMock));
    } catch (GuzzleException $exception) {
    }
  }

  public function testDeleteEntity()
  {
    $uuid = '11111111-0000-0000-0000-000000000000';
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('delete')
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    try {
      $this->assertEquals($jsonResponses[200], $this->contentHubClient->deleteEntity($uuid));
      $this->assertEquals($jsonResponses[404], $this->contentHubClient->deleteEntity($uuid));
    } catch (GuzzleException $exception) {
    }
  }

  public function testDeleteInterest()
  {
    $uuid = '11111111-0000-0000-0000-000000000000';
    $webhookUuid = '11111111-0000-0000-0000-000000000000';
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('delete')
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    try {
      $this->assertEquals($jsonResponses[200], $this->contentHubClient->deleteInterest($uuid, $webhookUuid));
      $this->assertEquals($jsonResponses[404], $this->contentHubClient->deleteInterest($uuid, $webhookUuid));
    } catch (GuzzleException $exception) {
    }
  }

  public function testPurge()
  {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);

    $this->contentHubClient->shouldReceive('post')
      ->andReturn($responseMock);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    try {
      $this->assertEquals($jsonResponses[200], $this->contentHubClient->purge());
      $this->assertEquals($jsonResponses[404], $this->contentHubClient->purge());
    } catch (GuzzleException $exception) {
    } catch (\Exception $exception) {
    }
  }

  public function testRestore()
  {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);

    $this->contentHubClient->shouldReceive('post')
      ->andReturn($responseMock);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    try {
      $this->assertEquals($jsonResponses[200], $this->contentHubClient->restore());
      $this->assertEquals($jsonResponses[404], $this->contentHubClient->restore());
    } catch (GuzzleException $exception) {
    } catch (\Exception $exception) {
    }
  }

  public function testReindex()
  {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);

    $this->contentHubClient->shouldReceive('post')
      ->andReturn($responseMock);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    try {
      $this->assertEquals($jsonResponses[200], $this->contentHubClient->reindex());
      $this->assertEquals($jsonResponses[404], $this->contentHubClient->reindex());
    } catch (GuzzleException $exception) {
    } catch (\Exception $exception) {
    }
  }

  public function testGetResponseJson()
  {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);
    $responseMock->shouldReceive('getBody')
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    try {
      $this->assertEquals($jsonResponses[200], ContentHubClient::getResponseJson($responseMock));
      $this->assertEquals($jsonResponses[404], ContentHubClient::getResponseJson($responseMock));
    } catch (GuzzleException $exception) {
    } catch (\Exception $exception) {
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
