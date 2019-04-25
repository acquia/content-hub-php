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

  /**
   * @throws GuzzleException
   */
  public function testDefinition()
  {
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('getResponseJson')
      ->andReturn($jsonResponses[200]);
    $responseMock = \Mockery::mock(Response::class);
    $this->contentHubClient->shouldReceive('request')
      ->andReturn($responseMock);

    $this->assertEquals($jsonResponses[200], $this->contentHubClient->definition('dummyendpoint'));
    $this->assertNotEquals($jsonResponses[404], $this->contentHubClient->definition('dummyendpoint'));
  }

  /**
   * @throws GuzzleException
   */
  public function testPutEntity()
  {
    $cdfObjectMock = $this->getCdfObjectMock();

    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('put')
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    $this->assertEquals($jsonResponses[200], $this->contentHubClient->putEntities($cdfObjectMock));
    $this->assertEquals($jsonResponses[404], $this->contentHubClient->putEntities($cdfObjectMock));
  }

  /**
   * @throws GuzzleException
   */
  public function testPutEntities()
  {
    $cdfObjectMock = $this->getCdfObjectMock();
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('put')
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    $this->assertEquals($jsonResponses[200], $this->contentHubClient->putEntity($cdfObjectMock));
    $this->assertEquals($jsonResponses[404], $this->contentHubClient->putEntity($cdfObjectMock));
  }

  /**
   * @throws GuzzleException
   */
  public function testPostEntities()
  {
    $cdfObjectMock = $this->getCdfObjectMock();
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('post')
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    $this->assertEquals($jsonResponses[200], $this->contentHubClient->postEntities($cdfObjectMock));
    $this->assertEquals($jsonResponses[404], $this->contentHubClient->postEntities($cdfObjectMock));
  }

  /**
   * @throws GuzzleException
   */
  public function testDeleteEntity()
  {
    $uuid = '11111111-0000-0000-0000-000000000000';
    $jsonResponses = $this->getJsonResponses();

    $this->contentHubClient->shouldReceive('delete')
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    $this->assertEquals($jsonResponses[200], $this->contentHubClient->deleteEntity($uuid));
    $this->assertEquals($jsonResponses[404], $this->contentHubClient->deleteEntity($uuid));
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
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    $this->assertEquals($jsonResponses[200], $this->contentHubClient->deleteInterest($uuid, $webhookUuid));
    $this->assertEquals($jsonResponses[404], $this->contentHubClient->deleteInterest($uuid, $webhookUuid));
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
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    $this->assertEquals($jsonResponses[200], $this->contentHubClient->purge());
    $this->assertEquals($jsonResponses[404], $this->contentHubClient->purge());
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
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    $this->assertEquals($jsonResponses[200], $this->contentHubClient->restore());
    $this->assertEquals($jsonResponses[404], $this->contentHubClient->restore());
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
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    $this->assertEquals($jsonResponses[200], $this->contentHubClient->reindex());
    $this->assertEquals($jsonResponses[404], $this->contentHubClient->reindex());
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
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404]]);

    $this->assertEquals($jsonResponses[200], ContentHubClient::getResponseJson($responseMock));
    $this->assertEquals($jsonResponses[404], ContentHubClient::getResponseJson($responseMock));
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
      ->andReturnValues([$jsonResponses[200], $jsonResponses[404], $jsonResponses[200]]);

    $this->assertEquals($jsonResponses[200], $this->contentHubClient->deleteClient($uuid));
    $this->assertEquals($jsonResponses[404], $this->contentHubClient->deleteClient($uuid));
    $this->expectException(\Exception::class);
    $this->contentHubClient->deleteClient($uuid);
  }

  /**
   * @return array
   */
  public function getJsonResponses()
  {
    return [
      200 => \GuzzleHttp\json_encode([
        'response_code' => 200,
      ]),
      404 => \GuzzleHttp\json_encode([
        'response_code' => 404,
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
