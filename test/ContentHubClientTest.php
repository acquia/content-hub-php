<?php

namespace Acquia\ContentHubClient\test;


use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\ContentHubClient;
use Acquia\ContentHubClient\Settings;
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
   * @const int
   */
  const HTTP_SUCCESS_RESPONSE = 200;

  /**
   * @const int
   */
  const HTTP_FAILURE_RESPONSE = 404;

  /**
   * {@inheritdoc}
   */
  public function setUp() : void
  {
    parent::setUp();

    $this->contentHubClient = \Mockery::mock(ContentHubClient::class)
      ->makePartial();
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() : void
  {
    parent::tearDown();
    unset($this->contentHubClient);
    \Mockery::close();
  }

  /**
   * @throws GuzzleException
   */
  public function testDefinition()
  {
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([$jsonResponses[self::HTTP_SUCCESS_RESPONSE], $jsonResponses[self::HTTP_FAILURE_RESPONSE]]);
    $responseMock = \Mockery::mock(Response::class);
    $this->contentHubClient->shouldReceive('request')
      ->andReturn($responseMock);

    $this->assertEquals($jsonResponses[self::HTTP_SUCCESS_RESPONSE], $this->contentHubClient->definition('dummyendpoint'));
    $this->assertEquals($jsonResponses[self::HTTP_FAILURE_RESPONSE], $this->contentHubClient->definition('dummyendpoint'));
  }

  /**
   * @throws GuzzleException
   */
  public function testPutEntity()
  {
    $cdfObjectMock = $this->getCdfObjectMock();

    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('put')
      ->andReturnValues([$jsonResponses[self::HTTP_SUCCESS_RESPONSE], $jsonResponses[self::HTTP_FAILURE_RESPONSE]]);

    $this->assertEquals($jsonResponses[self::HTTP_SUCCESS_RESPONSE], $this->contentHubClient->putEntities($cdfObjectMock));
    $this->assertEquals($jsonResponses[self::HTTP_FAILURE_RESPONSE], $this->contentHubClient->putEntities($cdfObjectMock));
  }

  /**
   * @throws GuzzleException
   */
  public function testPutEntities()
  {
    $cdfObjectMock = $this->getCdfObjectMock();
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('put')
      ->andReturnValues([$jsonResponses[self::HTTP_SUCCESS_RESPONSE], $jsonResponses[self::HTTP_FAILURE_RESPONSE]]);

    $this->assertEquals($jsonResponses[self::HTTP_SUCCESS_RESPONSE], $this->contentHubClient->putEntity($cdfObjectMock));
    $this->assertEquals($jsonResponses[self::HTTP_FAILURE_RESPONSE], $this->contentHubClient->putEntity($cdfObjectMock));
  }

  /**
   * @throws GuzzleException
   */
  public function testPostEntities()
  {
    $cdfObjectMock = $this->getCdfObjectMock();
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('post')
      ->andReturnValues([$jsonResponses[self::HTTP_SUCCESS_RESPONSE], $jsonResponses[self::HTTP_FAILURE_RESPONSE]]);

    $this->assertEquals($jsonResponses[self::HTTP_SUCCESS_RESPONSE], $this->contentHubClient->postEntities($cdfObjectMock));
    $this->assertEquals($jsonResponses[self::HTTP_FAILURE_RESPONSE], $this->contentHubClient->postEntities($cdfObjectMock));
  }

  /**
   * @throws GuzzleException
   */
  public function testDeleteEntity()
  {
    $uuid = '11111111-0000-0000-0000-000000000000';
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('delete')
      ->andReturnValues([$jsonResponses[self::HTTP_SUCCESS_RESPONSE], $jsonResponses[self::HTTP_FAILURE_RESPONSE]]);

    $this->assertEquals($jsonResponses[self::HTTP_SUCCESS_RESPONSE], $this->contentHubClient->deleteEntity($uuid));
    $this->assertEquals($jsonResponses[self::HTTP_FAILURE_RESPONSE], $this->contentHubClient->deleteEntity($uuid));
  }

  /**
   * @throws GuzzleException
   */
  public function testDeleteInterest()
  {
    $uuid = '11111111-0000-0000-0000-000000000000';
    $webhookUuid = '11111111-0000-0000-0000-000000000000';
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('delete')
      ->andReturnValues([$jsonResponses[self::HTTP_SUCCESS_RESPONSE], $jsonResponses[self::HTTP_FAILURE_RESPONSE]]);

    $this->assertEquals($jsonResponses[self::HTTP_SUCCESS_RESPONSE], $this->contentHubClient->deleteInterest($uuid, $webhookUuid));
    $this->assertEquals($jsonResponses[self::HTTP_FAILURE_RESPONSE], $this->contentHubClient->deleteInterest($uuid, $webhookUuid));
  }

  /**
   * @throws GuzzleException
   * @throws \Exception
   */
  public function testPurge()
  {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);

    $this->contentHubClient->shouldReceive('post')
      ->andReturn($responseMock);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([$jsonResponses[self::HTTP_SUCCESS_RESPONSE], $jsonResponses[self::HTTP_FAILURE_RESPONSE]]);

    $this->assertEquals($jsonResponses[self::HTTP_SUCCESS_RESPONSE], $this->contentHubClient->purge());
    $this->assertEquals($jsonResponses[self::HTTP_FAILURE_RESPONSE], $this->contentHubClient->purge());
  }

  /**
   * @throws GuzzleException
   * @throws \Exception
   */
  public function testRestore()
  {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);

    $this->contentHubClient->shouldReceive('post')
      ->andReturn($responseMock);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([$jsonResponses[self::HTTP_SUCCESS_RESPONSE], $jsonResponses[self::HTTP_FAILURE_RESPONSE]]);

    $this->assertEquals($jsonResponses[self::HTTP_SUCCESS_RESPONSE], $this->contentHubClient->restore());
    $this->assertEquals($jsonResponses[self::HTTP_FAILURE_RESPONSE], $this->contentHubClient->restore());
  }

  /**
   * @throws GuzzleException
   * @throws \Exception
   */
  public function testReindex()
  {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);

    $this->contentHubClient->shouldReceive('post')
      ->andReturn($responseMock);
    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturnValues([$jsonResponses[self::HTTP_SUCCESS_RESPONSE], $jsonResponses[self::HTTP_FAILURE_RESPONSE]]);

    $this->assertEquals($jsonResponses[self::HTTP_SUCCESS_RESPONSE], $this->contentHubClient->reindex());
    $this->assertEquals($jsonResponses[self::HTTP_FAILURE_RESPONSE], $this->contentHubClient->reindex());
  }

  /**
   * @throws GuzzleException
   * @throws \Exception
   */
  public function testGetResponseJson()
  {
    $jsonResponses = $this->getJsonResponses();
    $responseMock = \Mockery::mock(ResponseInterface::class);
    $responseMock->shouldReceive('getBody')
      ->andReturnValues([$jsonResponses[self::HTTP_SUCCESS_RESPONSE], $jsonResponses[self::HTTP_FAILURE_RESPONSE]]);
    $this->assertEquals(json_decode($jsonResponses[self::HTTP_SUCCESS_RESPONSE], true), ContentHubClient::getResponseJson($responseMock));
    $this->assertEquals(json_decode($jsonResponses[self::HTTP_FAILURE_RESPONSE], true), ContentHubClient::getResponseJson($responseMock));
  }

  /**
   * @throws GuzzleException
   * @throws \Exception
   */
  public function testDeleteClient()
  {
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
      ->andReturnValues([true, true, false]);

    $this->contentHubClient->shouldReceive('delete')
      ->andReturnValues([$jsonResponses[self::HTTP_SUCCESS_RESPONSE], $jsonResponses[self::HTTP_FAILURE_RESPONSE], $jsonResponses[self::HTTP_SUCCESS_RESPONSE]]);

    $this->assertEquals($jsonResponses[self::HTTP_SUCCESS_RESPONSE], $this->contentHubClient->deleteClient($uuid));
    $this->assertEquals($jsonResponses[self::HTTP_FAILURE_RESPONSE], $this->contentHubClient->deleteClient($uuid));
    $this->expectException(\Exception::class);
    $this->contentHubClient->deleteClient($uuid);
  }

  /**
   * @return array
   */
  public function getJsonResponses()
  {
    return [
      self::HTTP_SUCCESS_RESPONSE => \GuzzleHttp\json_encode([
        'response_code' => self::HTTP_SUCCESS_RESPONSE,
      ]),
      self::HTTP_FAILURE_RESPONSE => \GuzzleHttp\json_encode([
        'response_code' => self::HTTP_FAILURE_RESPONSE,
      ]),
    ];
  }

  /**
   * @return mixed
   */
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
