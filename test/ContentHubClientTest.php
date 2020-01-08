<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\ContentHubClient;
use Acquia\ContentHubClient\Settings;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Class ContentHubClientTest.
 *
 * @package Acquia\ContentHubClient\test
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ContentHubClientTest extends TestCase {

  /**
   * ContentHubClient Mock Object.
   *
   * @var \Acquia\ContentHubClient\ContentHubClient
   */
  private $contentHubClient;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->contentHubClient = \Mockery::mock(ContentHubClient::class)
      ->makePartial();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown(): void {
    parent::tearDown();
    unset($this->contentHubClient);
    \Mockery::close();
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::definition
   *
   * @throws \Exception
   */
  public function testDefinition() {
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      ]);
    $responseMock = \Mockery::mock(Response::class);
    $this->contentHubClient->shouldReceive('request')
      ->andReturn($responseMock);

    $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->definition('dummyendpoint'));
    $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->definition('dummyendpoint'));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::putEntities
   */
  public function testPutEntity() {
    $cdfObjectMock = $this->getCdfObjectMock();

    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('put')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      ]);

    $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->putEntities($cdfObjectMock));
    $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->putEntities($cdfObjectMock));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::putEntity
   */
  public function testPutEntities() {
    $cdfObjectMock = $this->getCdfObjectMock();
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('put')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      ]);

    $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->putEntity($cdfObjectMock));
    $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->putEntity($cdfObjectMock));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::postEntities
   */
  public function testPostEntities() {
    $cdfObjectMock = $this->getCdfObjectMock();
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('post')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      ]);

    $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->postEntities($cdfObjectMock));
    $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->postEntities($cdfObjectMock));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::deleteEntity
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function testDeleteEntity() {
    $uuid = '11111111-0000-0000-0000-000000000000';
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('delete')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      ]);

    $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->deleteEntity($uuid));
    $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->deleteEntity($uuid));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::deleteInterest
   */
  public function testDeleteInterest() {
    $uuid = '11111111-0000-0000-0000-000000000000';
    $webhookUuid = '11111111-0000-0000-0000-000000000000';
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('delete')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      ]);

    $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->deleteInterest($uuid, $webhookUuid));
    $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->deleteInterest($uuid, $webhookUuid));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::purge
   *
   * @throws \Exception
   */
  public function testPurge() {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);

    $this->contentHubClient->shouldReceive('post')
      ->andReturn($responseMock);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      ]);

    $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->purge());
    $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->purge());
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::restore
   *
   * @throws \Exception
   */
  public function testRestore() {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);

    $this->contentHubClient->shouldReceive('post')
      ->andReturn($responseMock);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      ]);

    $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->restore());
    $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->restore());
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::reindex
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Exception
   */
  public function testReindex() {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);

    $this->contentHubClient->shouldReceive('post')
      ->andReturn($responseMock);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      ]);

    $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->reindex());
    $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->reindex());
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getResponseJson
   *
   * @throws \Exception
   */
  public function testGetResponseJson() {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);
    $responseMock->shouldReceive('getBody')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      ]);
    $this->assertEquals(
      json_decode($jsonResponses[HttpResponse::HTTP_OK], TRUE),
      ContentHubClient::getResponseJson($responseMock)
    );
    $this->assertEquals(
      json_decode($jsonResponses[HttpResponse::HTTP_NOT_FOUND], TRUE),
      ContentHubClient::getResponseJson($responseMock)
    );
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::deleteClient
   *
   * @throws \Exception
   */
  public function testDeleteClient() {
    $jsonResponses = $this->getJsonResponses();
    $uuid = '11111111-0000-0000-0000-000000000000';

    $settingsMock = $this->getMockBuilder(Settings::class)
      ->disableOriginalConstructor()
      ->setMethods(['getUuid'])
      ->getMock();

    $settingsMock->expects($this->any())
      ->method('getUuid')
      ->willReturn($uuid);

    $this->contentHubClient->shouldReceive('getSettings')
      ->andReturn($settingsMock);

    $this->contentHubClient->shouldReceive('deleteEntity')
      ->andReturnValues([TRUE, TRUE, FALSE]);

    $this->contentHubClient->shouldReceive('delete')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
        $jsonResponses[HttpResponse::HTTP_OK],
      ]);

    $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->deleteClient($uuid));
    $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->deleteClient($uuid));
    $this->expectException(\Exception::class);
    $this->contentHubClient->deleteClient($uuid);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::addFilterToWebhook
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Exception
   */
  public function testAddFilterToWebhook() {
    $filterId = '11111111-0000-0000-0000-000000000000';
    $webhookId = '22222222-0000-0000-0000-000000000000';
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);

    $this->contentHubClient->shouldReceive('post')
      ->andReturn($responseMock);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      ]);

    $this->assertEquals(
      $jsonResponses[HttpResponse::HTTP_OK],
      $this->contentHubClient->addFilterToWebhook($filterId, $webhookId)
    );
    $this->assertEquals(
      $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      $this->contentHubClient->addFilterToWebhook($filterId, $webhookId)
    );
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::putFilter
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Exception
   */
  public function testPutFilter() {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);

    $this->contentHubClient->shouldReceive('put')
      ->andReturn($responseMock);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      ]);

    $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->putFilter(''));
    $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->putFilter(''));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::listEntities
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Exception
   */
  public function testListEntities() {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);

    $this->contentHubClient->shouldReceive('get')
      ->andReturn($responseMock);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([
        $jsonResponses[HttpResponse::HTTP_OK],
        $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
      ]);

    $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->listEntities());
    $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->listEntities());
  }

  /**
   * Returns responses array.
   *
   * @return array
   *   Responses array.
   */
  public function getJsonResponses() {
    return [
      HttpResponse::HTTP_OK => \GuzzleHttp\json_encode([
        'response_code' => HttpResponse::HTTP_OK,
      ]),
      HttpResponse::HTTP_NOT_FOUND => \GuzzleHttp\json_encode([
        'response_code' => HttpResponse::HTTP_NOT_FOUND,
      ]),
    ];
  }

  /**
   * Returns CDFObject mock.
   *
   * @return mixed
   *   CDFObject mock.
   */
  public function getCdfObjectMock() {
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
