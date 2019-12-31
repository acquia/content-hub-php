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
   * {@inheritdoc}
   */
    public function setUp(): void
    {
        parent::setUp();

        $this->contentHubClient = \Mockery::mock(ContentHubClient::class)
          ->makePartial();
    }

  /**
   * {@inheritdoc}
   */
    public function tearDown(): void
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
   * @throws GuzzleException
   */
    public function testPutEntity()
    {
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
   * @throws GuzzleException
   */
    public function testPutEntities()
    {
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
   * @throws GuzzleException
   */
    public function testPostEntities()
    {
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
   * @throws GuzzleException
   */
    public function testDeleteEntity()
    {
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
   * @throws GuzzleException
   */
    public function testDeleteInterest()
    {
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
          ->andReturnValues([
            $jsonResponses[HttpResponse::HTTP_OK],
            $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
          ]);

        $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->purge());
        $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->purge());
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
          ->andReturnValues([
            $jsonResponses[HttpResponse::HTTP_OK],
            $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
          ]);

        $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->restore());
        $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->restore());
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
          ->andReturnValues([
            $jsonResponses[HttpResponse::HTTP_OK],
            $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
          ]);

        $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->reindex());
        $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->reindex());
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
          ->andReturnValues([
            $jsonResponses[HttpResponse::HTTP_OK],
            $jsonResponses[HttpResponse::HTTP_NOT_FOUND],
          ]);
        $this->assertEquals(json_decode($jsonResponses[HttpResponse::HTTP_OK], true), ContentHubClient::getResponseJson($responseMock));
        $this->assertEquals(json_decode($jsonResponses[HttpResponse::HTTP_NOT_FOUND], true), ContentHubClient::getResponseJson($responseMock));
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
   * @throws GuzzleException
   * @throws \Exception
   */
    public function testAddFilterToWebhook()
    {
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

        $this->assertEquals($jsonResponses[HttpResponse::HTTP_OK], $this->contentHubClient->addFilterToWebhook($filterId, $webhookId));
        $this->assertEquals($jsonResponses[HttpResponse::HTTP_NOT_FOUND], $this->contentHubClient->addFilterToWebhook($filterId, $webhookId));
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public function testPutFilter()
    {
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
   * @throws GuzzleException
   * @throws \Exception
   */
    public function testListEntities()
    {
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
   * @return array
   */
    public function getJsonResponses()
    {
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
