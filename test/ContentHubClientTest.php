<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDFDocument;
use Acquia\ContentHubClient\ContentHubClient;
use Acquia\ContentHubClient\ContentHubLibraryEvents;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use Acquia\ContentHubClient\ObjectFactory;
use Acquia\ContentHubClient\SearchCriteria\SearchCriteria;
use Acquia\ContentHubClient\Settings;
use Acquia\ContentHubClient\Webhook;
use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * @covers \Acquia\ContentHubClient\ContentHubClient
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ContentHubClientTest extends TestCase {

  /**
   * CH client.
   *
   * @var \Acquia\ContentHubClient\ContentHubClient
   */
  private $ch_client; // phpcs:ignore

  /**
   * Guzzle client.
   *
   * @var \GuzzleHttp\Client|\Mockery\MockInterface
   */
  private $guzzle_client; // phpcs:ignore

  /**
   * Test data.
   *
   * @var array
   */
  private $test_data; // phpcs:ignore

  /**
   * Object factory.
   *
   * @var \Mockery\MockInterface
   */
  private $object_factory; // phpcs:ignore

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $dispatcher;

  /**
   * Test data.
   *
   * @var array
   */
  private $cdf1_array; // phpcs:ignore

  /**
   * Test data.
   *
   * @var array
   */
  private $cdf2_array; // phpcs:ignore

  /**
   * CDFObject instance.
   *
   * @var \Acquia\ContentHubClient\CDF\CDFObject
   */
  private $cdf1;

  /**
   * CDFObject instance.
   *
   * @var \Acquia\ContentHubClient\CDF\CDFObject
   */
  private $cdf2;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->test_data = [
      'name' => 'some-name',
      'uuid' => 'some-uuid',
      'client-uuid' => 'client-uuid',
      'api-key' => 'some-api-key',
      'secret-key' => 'some-secret-key',
      'url' => 'https://some-url/',
      'api-version' => '//v2//',
      'host-name' => 'some-host-name',
      'shared-secret' => 'some-shared-secret',
      'webhook-uuid' => 'some-webhook-uuid',
      'clients' => [
        [
          'name' => 'client-1',
          'uuid' => 'client-1-uuid',
        ],
        [
          'name' => 'client-2',
          'uuid' => 'client-2-uuid',
        ],
      ],
      'webhooks' => [
        [
          'uuid' => 'some-webhook-uuid',
          'client_uuid' => 'some-client-id',
          'client_name' => 'some-client-name',
          'url' => 'some-webhook-url',
          'version' => 2,
          'disable_retries' => FALSE,
          'filters' => [
            'filter-1-uuid',
          ],
          'status' => 'ENABLED',
          'is_migrated' => FALSE,
          'suppressed_until' => 'some-timestamp',
        ],
      ],
    ];

    $this->cdf1_array = [
      'type' => 'some-type-1',
      'uuid' => 'some-uuid-1',
      'created' => 'some-creation-date-1',
      'modified' => 'some-modified-date-1',
      'origin' => 'some-origin-1',
      'metadata' => [],
      'attributes' => [],
    ];
    $this->cdf2_array = [
      'type' => 'some-type-2',
      'uuid' => 'some-uuid-2',
      'created' => 'some-creation-date-2',
      'modified' => 'some-modified-date-2',
      'origin' => 'some-origin-2',
      'metadata' => [],
      'attributes' => [],
    ];

    $this->cdf1 = \Mockery::mock(CDFObject::class);
    $this->cdf1->shouldReceive('toArray')->andReturn($this->cdf1_array);
    $this->cdf2 = \Mockery::mock(CDFObject::class);
    $this->cdf2->shouldReceive('toArray')->andReturn($this->cdf2_array);

    $handler_stack = \Mockery::mock(HandlerStack::class);
    $mock_hmac_middleware = \Mockery::mock(HmacAuthMiddleware::class);

    $this->guzzle_client = \Mockery::mock(Client::class);
    $this->object_factory = \Mockery::mock('alias:' . ObjectFactory::class);

    $this->dispatcher = $this->getMockDispatcher();

    $this->ch_client = $this->makeMockCHClient(
      [
        'base_url' => $this->test_data['url'],
        'client-languages' => [
          'en',
          'es',
          'und',
        ],
      ],
      new NullLogger(),
      $this->makeMockSettings(
        $this->test_data['name'],
        $this->test_data['uuid'],
        $this->test_data['api-key'],
        $this->test_data['secret-key'],
        $this->test_data['url'],
        $this->test_data['shared-secret'],
        []
      ),
      \Mockery::mock(HmacAuthMiddleware::class),
      $this->dispatcher,
      'v2'
    );

    $this->test_data['uri'] = $this->ch_client::makeBaseURL($this->test_data['url'], $this->test_data['api-version']);

    $this->object_factory->shouldReceive('getHmacAuthMiddleware')
      ->andReturn($mock_hmac_middleware);
    $this->object_factory->shouldReceive('getHandlerStack')
      ->andReturn($handler_stack);
    $this->object_factory->shouldReceive('getGuzzleClient')
      ->andReturnUsing(function (array $config) {
        $this->guzzle_client->shouldReceive('getConfig')->andReturn($config);
        return $this->guzzle_client;
      });

    $this->object_factory->shouldReceive('getAuthenticationKey')
      ->andReturn(\Mockery::mock(Key::class));
    $this->object_factory->shouldReceive('instantiateSettings')
      ->andReturnUsing(function (
        string $name,
        string $uuid,
        string $api_key,
        string $secret,
        string $url,
        ?string $shared_secret = NULL,
        array $webhook = []
      ) {
        return $this->makeMockSettings($name, $uuid, $api_key, $secret, $url,
          $shared_secret, $webhook);
      });
    $this->object_factory->shouldReceive('getCHClient')
      ->andReturnUsing(function (
        array $config,
        LoggerInterface $logger,
        Settings $settings,
        HmacAuthMiddleware $middleware,
        EventDispatcherInterface $dispatcher,
        string $api_version = 'v2'
      ) {
        return $this->makeMockCHClient($config, $logger, $settings, $middleware,
          $dispatcher, $api_version);
      });
    $this->object_factory->shouldReceive('getCDFDocument')
      ->andReturnUsing(function (...$entities) {
        return $this->makeMockCDFDocument(...$entities);
      });
    $this->object_factory->shouldReceive('getCDFTypeEvent')
      ->andReturnUsing(function (array $data) {
        return $this->makeMockCdfTypeEvent($data);
      });
    $this->object_factory->shouldReceive('getWebhook')
      ->andReturnUsing(function (array $definition) {
        return $this->makeMockWebhook($definition);
      });

    $handler_stack->shouldReceive('push')
      ->andReturn(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown(): void {
    parent::tearDown();

    unset($this->ch_client);
    \Mockery::close();
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::ping
   * @throws \Exception
   */
  public function testPing(): void {
    $response_body = [
      'success' => TRUE,
      'request_id' => 'some-uuid',
    ];
    $config = [
      'base_uri' => $this->test_data['url'],
    ];

    $this->guzzle_client
      ->shouldReceive('get')
      ->once()
      ->with('ping')
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response_body)));

    $this->assertSame($this->ch_client->ping(), $response_body);
    $this->assertSame($this->guzzle_client->getConfig(), $config);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::register
   * @throws \Exception
   */
  public function testSuccessfulRegistration(): void {
    $response_body = [
      'name' => $this->test_data['name'],
      'uuid' => $this->test_data['client-uuid'],
    ];
    $this->guzzle_client
      ->shouldReceive('post')
      ->once()
      ->with('register', ['body' => json_encode(['name' => $this->test_data['name']])])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response_body)));

    $response = $this->makeRegistrationRequest(new NullLogger());

    $this->assertResponseItems($response);
    $this->assertGuzzleConfig();
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::register
   * @throws \Exception
   */
  public function testRegistrationFailsIfCallToRegisterThrowsBadResponseException(): void {
    $request = new Request('post', 'register');
    $response = new Response(SymfonyResponse::HTTP_BAD_REQUEST);
    $this->guzzle_client
      ->shouldReceive('post')
      ->once()
      ->with('register', ['body' => json_encode(['name' => $this->test_data['name']])])
      ->andThrow(new BadResponseException('Some message', $request, $response));

    $this->expectException(RequestException::class);

    $this->makeRegistrationRequest($this->getMockLogger('error'));
    $this->assertGuzzleConfig();
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::register
   * @throws \Exception
   */
  public function testRegistrationFailsWhenUnauthorizedCallToRegisterIsMade(): void {
    $request = new Request('post', 'register');
    $response = new Response(SymfonyResponse::HTTP_BAD_REQUEST);
    $this->guzzle_client
      ->shouldReceive('post')
      ->once()
      ->with('register', ['body' => json_encode(['name' => $this->test_data['name']])])
      ->andThrow(new RequestException('Some message', $request, $response));

    $this->expectException(RequestException::class);

    $this->makeRegistrationRequest($this->getMockLogger('error'));
    $this->assertGuzzleConfig();
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::register
   * @throws \Exception
   */
  public function testRegistrationFailsWhenRegisterThrowsAnException(): void {
    $this->guzzle_client
      ->shouldReceive('post')
      ->once()
      ->with('register', ['body' => json_encode(['name' => $this->test_data['name']])])
      ->andThrow(new \Exception());

    $this->expectException(\Exception::class);

    $this->makeRegistrationRequest($this->getMockLogger('error'));
    $this->assertGuzzleConfig();
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::clientNameExists
   */
  public function testClientNameExistsReturnsTrueIfSuccessful(): void {
    $this->guzzle_client
      ->shouldReceive('get')
      ->once()
      ->with('settings/clients/' . $this->test_data['name']);

    $exists = $this->ch_client::clientNameExists(
      $this->test_data['name'],
      $this->test_data['url'],
      $this->test_data['api-key'],
      $this->test_data['secret-key'],
      $this->test_data['api-version']
    );

    $this->assertTrue($exists);
    $config = $this->guzzle_client->getConfig();

    $this->assertSame($config['base_uri'], $this->test_data['uri']);
    $this->assertSame($config['headers']['Content-Type'], 'application/json');
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::clientNameExists
   */
  public function testClientNameExistsReturnsFalseIfCallToPlexusFails(): void {
    $request = \Mockery::mock(Request::class);
    $response = $this->makeMockResponse(SymfonyResponse::HTTP_NOT_FOUND, [], json_encode(FALSE));
    $this->guzzle_client
      ->shouldReceive('get')
      ->once()
      ->with('settings/clients/' . $this->test_data['name'])
      ->andThrows(new ClientException('some-message', $request, $response));

    $exists = $this->ch_client::clientNameExists(
      $this->test_data['name'],
      $this->test_data['url'],
      $this->test_data['api-key'],
      $this->test_data['secret-key'],
      $this->test_data['api-version']
    );

    $this->assertFalse($exists);
    $config = $this->guzzle_client->getConfig();

    $this->assertSame($config['base_uri'], $this->test_data['uri']);
    $this->assertSame($config['headers']['Content-Type'], 'application/json');
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::createEntities
   * @throws \Exception
   */
  public function testCreateEntities(): void {
    $request_body = [
      'resource' => '',
      'entities' => [
        $this->cdf1_array,
        $this->cdf2_array,
      ],
    ];

    $response_code = SymfonyResponse::HTTP_ACCEPTED;
    $response_body = [
      'success' => TRUE,
    ];
    $response = $this->makeMockResponse($response_code, [], json_encode($response_body));

    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with('entities', ['body' => json_encode($request_body)])
      ->andReturn($response);

    $result = $this->ch_client->createEntities($this->cdf1, $this->cdf2);

    $this->assertSame($result->getStatusCode(), $response_code);
    $this->assertSame($response_body, $this->ch_client::getResponseJson($result));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getEntity
   * @throws \ReflectionException
   */
  public function testGetEntityReturnsCDFObjectUponSuccess(): void { // phpcs:ignore
    $uuid = 'some-existing-entity-uuid';
    $response_body = [
      'success' => TRUE,
      'data' => [
        'data' => [
          'uuid' => 'some-uuid',
          'type' => 'some-type',
        ],
      ],
    ];
    $response = $this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response_body));

    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('entities/' . $uuid)
      ->andReturn($response);

    $this->ch_client->shouldReceive('getCDFObject')
      ->andReturn(\Mockery::mock(CDFObject::class));

    $this->assertInstanceOf(CDFObject::class, $this->ch_client->getEntity($uuid));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getEntity
   * @throws \ReflectionException
   */
  public function testGetEntityReturnsSimpleObjectUponFailure(): void {
    $uuid = 'some-non-existing-entity-uuid';
    $response_body = [
      'success' => FALSE,
      'error' => [
        'code' => 10,
        'message' => 'some-error-message',
      ],
      'request_id' => 'request-uuid',
    ];
    $response = $this->makeMockResponse(SymfonyResponse::HTTP_NOT_FOUND, [], json_encode($response_body));

    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('entities/' . $uuid)
      ->andReturn($response);

    $this->assertSame($response_body, $this->ch_client->getEntity($uuid));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getEntities
   * @throws \ReflectionException
   */
  public function testGetEntitiesChunksUpUUIDsToSetOf50sAndReturnACDFDocument(): void { // phpcs:ignore
    $total = 56;
    $chunk_size = 50;
    $uuids = array_fill(0, $total, 'some-existing-uuid');

    $this->ch_client->shouldReceive('getCDFObject')
      ->andReturn(\Mockery::mock(CDFObject::class));

    foreach (array_chunk($uuids, $chunk_size) as $chunk) {
      $call_params = [
        'size' => $chunk_size,
        'query' => [
          'constant_score' => [
            'filter' => [
              'terms' => [
                'uuid' => $chunk,
              ],
            ],
          ],
        ],
      ];
      $this->ch_client
        ->shouldReceive('get')
        ->once()
        ->with('_search', ['body' => json_encode($call_params)])
        ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [],
          json_encode([
            'hits' => [
              'hits' => array_fill(0, count($chunk),
                $this->getElasticSearchItemWithId()),
              'max_score' => 1,
              'total' => count($chunk),
            ],
          ])));
    }

    $result = $this->ch_client->getEntities($uuids);

    $this->assertInstanceOf(CDFDocument::class, $result);
    $this->assertCount($total, $result->getEntities());
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getEntities
   * @throws \ReflectionException
   */
  public function testGetEntitiesReturnsCDFDocumentWithEmptyObjectSetIfNothingFound(): void { // phpcs:ignore
    $total = 56;
    $chunk_size = 50;
    $uuids = array_fill(0, $total, 'some-non-existing-uuid');

    foreach (array_chunk($uuids, $chunk_size) as $chunk) {
      $call_params = [
        'size' => $chunk_size,
        'query' => [
          'constant_score' => [
            'filter' => [
              'terms' => [
                'uuid' => $chunk,
              ],
            ],
          ],
        ],
      ];
      $this->ch_client
        ->shouldReceive('get')
        ->once()
        ->with('_search', ['body' => json_encode($call_params)])
        ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [],
          json_encode([
            'hits' => [
              'hits' => [],
              'max_score' => NULL,
              'total' => 0,
            ],
          ])));
    }

    $result = $this->ch_client->getEntities($uuids);

    $this->assertInstanceOf(CDFDocument::class, $result);
    $this->assertCount(0, $result->getEntities());
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getCDFObject
   * @throws \ReflectionException
   */
  public function testGetCDFObjectAlsoDispatchesGetCDFClassEvent(): void { // phpcs:ignore
    $data = [
      'type' => 'some-type-1',
      'uuid' => 'some-uuid-1',
      'created' => 'some-creation-date-1',
      'modified' => 'some-modified-date-1',
      'origin' => 'some-origin-1',
      'metadata' => [],
      'attributes' => [],
    ];
    $this->dispatcher
      ->shouldReceive('dispatch')
      ->once()
      ->withArgs(static function (string $event_name, Event $event) {
        return $event_name === ContentHubLibraryEvents::GET_CDF_CLASS;
      });

    $result = $this->ch_client->getCDFObject($data);
    $this->assertInstanceOf(CDFObject::class, $result);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::putEntities
   * @throws \Exception
   */
  public function testPutEntitiesReturnsSuccessIfAllGoesWell(): void {
    $response_code = SymfonyResponse::HTTP_ACCEPTED;
    $response_body = [
      'success' => TRUE,
      'request_id' => 'some-uuid',
    ];

    $request_body = [
      'resource' => '',
      'data' => [
        'entities' => [
          $this->cdf1_array,
          $this->cdf2_array,
        ],
      ],
    ];

    $this->ch_client
      ->shouldReceive('put')
      ->once()
      ->with('entities', ['body' => json_encode($request_body)])
      ->andReturn($this->makeMockResponse($response_code, [], json_encode($response_body)));

    $api_response = $this->ch_client->putEntities($this->cdf1, $this->cdf2);

    $this->assertSame($response_code, $api_response->getStatusCode());
    $this->assertSame($response_body, $this->ch_client::getResponseJson($api_response));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::postEntities
   */
  public function testPostEntitiesReturnHTTPAcceptedHeaderAndAnEmptyBodyIfAllGoesWell(): void { // phpcs:ignore
    $response_code = SymfonyResponse::HTTP_ACCEPTED;
    $request_body = [
      'resource' => '',
      'data' => [
        'entities' => [
          $this->cdf1_array,
          $this->cdf2_array,
        ],
      ],
    ];

    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with('entities', ['body' => json_encode($request_body)])
      ->andReturn($this->makeMockResponse($response_code, [], ''));

    $api_response = $this->ch_client->postEntities($this->cdf1, $this->cdf2);

    $this->assertSame($response_code, $api_response->getStatusCode());
    $this->assertSame('', $api_response->getBody()->getContents());
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::deleteEntity
   */
  public function testDeleteEntityReturnsHTTPDeletedIfAllGoesWell(): void { // phpcs:ignore
    $uuid = $this->test_data['uuid'];
    $response_code = SymfonyResponse::HTTP_ACCEPTED;

    $this->ch_client
      ->shouldReceive('delete')
      ->once()
      ->with('entities/' . $uuid)
      ->andReturn($this->makeMockResponse($response_code, [], ''));

    $api_response = $this->ch_client->deleteEntity($uuid);
    $this->assertSame($response_code, $api_response->getStatusCode());
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::deleteInterest
   */
  public function testDeleteInterestReturnsHTTPAcceptedIfAllGoesWell(): void { // phpcs:ignore
    $uuid = $this->test_data['uuid'];
    $webhook_uuid = $this->test_data['webhook-uuid'];
    $response_code = SymfonyResponse::HTTP_ACCEPTED;

    $this->ch_client
      ->shouldReceive('delete')
      ->once()
      ->with("interest/${uuid}/${webhook_uuid}")
      ->andReturn($this->makeMockResponse($response_code, [], ''));

    $api_response = $this->ch_client->deleteInterest($uuid, $webhook_uuid);
    $this->assertSame($response_code, $api_response->getStatusCode());
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::purge
   * @throws \Exception
   */
  public function testPurgeReturnsSuccess(): void {
    $response = [
      'success' => TRUE,
      'request_id' => 'some-uuid',
    ];

    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with('entities/purge')
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($response, $this->ch_client->purge());
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::restore
   * @throws \Exception
   */
  public function testRestoreReturnsSuccess(): void {
    $response = [
      'success' => TRUE,
      'request_id' => 'some-uuid',
    ];

    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with('entities/restore')
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($response, $this->ch_client->restore());
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::reindex
   * @throws \Exception
   */
  public function testReindexReturnsSuccess(): void {
    $response = [
      'success' => TRUE,
      'request_id' => 'some-uuid',
    ];

    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with('reindex')
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_ACCEPTED, [], json_encode($response)));

    $this->assertSame($response, $this->ch_client->reindex());
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::logs
   * @throws \Exception
   */
  public function testLogsReturnsDiscontinued(): void {
    $api_response = $this->ch_client->logs();

    $body = $this->ch_client::FEATURE_DEPRECATED_RESPONSE;
    $this->assertEquals($body['error']['code'], $api_response->getStatusCode());
    $this->assertEquals($body['error']['message'], $api_response->getReasonPhrase());
    $this->assertEquals($body, json_decode($api_response->getBody()->getContents(), TRUE));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::mapping
   * @throws \Exception
   */
  public function testMappingReturnsCorrectInfo(): void {
    $response = [
      'entity' =>
        [
          'dynamic' => 'true',
          'properties' =>
            [
              'data' => [
                'dynamic' => 'true',
                'properties' => [
                  'assets' => [],
                  'attributes' => [],
                  'created' => [],
                  'metadata' => [],
                  'modified' => [],
                  'origin' => [],
                  'type' => [],
                  'uuid' => [],
                ],
              ],
              'id' => [
                'type' => 'text',
              ],
              'origin' => [
                'type' => 'keyword',
              ],
              'revision' => [
                'type' => 'long',
              ],
              'subscription' => [
                'type' => 'text',
              ],
              'uuid' => [
                'type' => 'keyword',
              ],
            ],
        ],
    ];

    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('_mapping')
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $api_response = $this->ch_client->mapping();

    $this->assertSameSize($api_response['entity']['properties'], $response['entity']['properties']);
    $this->assertSameSize($api_response['entity']['properties']['data']['properties'], $response['entity']['properties']['data']['properties']);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::listEntities
   * @throws \Exception
   */
  public function testListEntities(): void {
    $return_fields = [
      'field_1',
      'field_2',
      'field_3',
    ];
    $filters = [
      'filter_1' => 'value_1',
      'filter_2' => 'value_2',
    ];

    $filter_query = [];

    foreach ($filters as $key => $value) {
      $filter_query["filter:${key}"] = $value;
    }

    $total = 2;
    $query_parameters = [
      'fields' => implode(',', $return_fields),
      'language' => 'en',
      'limit' => $total,
      'origin' => 'origin-uuid',
      'start' => 0,
      'type' => 'some-type',
    ];

    $data_item = [
      'uuid' => 'some-uuid',
      'origin' => 'some-uuid',
      'modified' => 'some-modification-date',
      'type' => 'some-type',
      'metadata' => [
        'data' => '',
        'default_language' => 'en',
        'dependencies' => [
          'modules' => [
            'module-1',
            'module-2',
          ],
        ],
      ],
    ];

    $response = [
      'success' => TRUE,
      'total' => $total,
      'data' => array_fill($query_parameters['start'], $total, $data_item),
    ];

    $query_string = http_build_query(array_merge($query_parameters, $filter_query));

    $query_parameters['filters'] = $filters;
    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('entities?' . $query_string)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $api_response = $this->ch_client->listEntities($query_parameters);
    $this->assertSame($total, $api_response['total']);
    $this->assertCount($total, $api_response['data']);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::searchEntity
   * @throws \Exception
   */
  public function testSearchEntityRetrievesEntityFromES(): void { // phpcs:ignore
    $query_params = [
      'from' => 0,
      'query' => [
        'bool' => [
          'filter' => [
            [
              'term' => ['data.type' => 'some-type'],
            ],
          ],
        ],
      ],
      'size' => 2,
      'sort' => [
        'data.modified' => 'desc',
      ],
    ];

    $result_item_type = 'some-type';
    $response = [
      'hits' => [
        'hits' => [
          $this->getElasticSearchItemWithId(1, $result_item_type),
          $this->getElasticSearchItemWithId(2, $result_item_type),
        ],
        'max_score' => 1,
      ],
    ];
    $response['hits']['total'] = count($response['hits']);

    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('_search', ['body' => json_encode($query_params)])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $api_response = $this->ch_client->searchEntity($query_params);
    $this->assertTrue(count($api_response['hits']['hits']) <= $query_params['size']);
    foreach ($api_response['hits']['hits'] as $key => $item) {
      $this->assertSame($item['_id'], $key + 1);
      $this->assertSame($item['_type'], $result_item_type);
    }
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getClientByName
   * @throws \Exception
   */
  public function testGetClientByNameReturnsClientInfoIfSuccessful(): void {
    $response = [
      'name' => $this->test_data['name'],
      'uuid' => $this->test_data['client-uuid'],
    ];
    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('settings/clients/' . $this->test_data['name'])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $api_response = $this->ch_client->getClientByName($this->test_data['name']);
    $this->assertSame($api_response, $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getClientByName
   * @throws \Exception
   */
  public function testGetClientByNameReturnsUnsuccessfulIfClientIsNotFound(): void {
    $response = [
      'success' => FALSE,
      'error' => [
        'code' => 4005,
        'message' => 'The requested client name was not found.',
      ],
      'request_id' => 'some-request-uuid',
    ];

    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('settings/clients/' . $this->test_data['name'])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_NOT_FOUND, [], json_encode($response)));

    $this->assertSame($this->ch_client->getClientByName($this->test_data['name']), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getClients
   * @throws \Exception
   */
  public function testGetClientsReturnsAnArrayOfClientsIfAny(): void {
    $this->assertSame($this->ch_client->getClients(), $this->test_data['clients']);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getWebHooks
   * @throws \Exception
   */
  public function testGetWebhooksReturnsAnArrayOfWebhooksIfAny(): void {
    $this->assertSame(current($this->ch_client->getWebHooks())->getDefinition(), current($this->test_data['webhooks']));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getInterestsByWebhook
   * @throws \Exception
   */
  public function testGetWebhookInterestListReturnsEmptyArrayIfNone(): void {
    $response = [
      'success' => FALSE,
      'error' => [
        'code' => 404,
        'message' => 'interests list is empty.',
        'request_id' => 'some-request-uuid',
      ],
    ];
    $webhook_uuid = 'some-webhook-uuid';
    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('interest/webhook/' . $webhook_uuid)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->getInterestsByWebhook($webhook_uuid), []);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getInterestsByWebhook
   * @throws \Exception
   */
  public function testGetWebhookInterestListReturnsAnArrayIfAny(): void {
    $response = [
      'success' => TRUE,
      'data' => [
        'count' => 2,
        'interests' => [
          'some-uuid-1',
          'some-uuid-2',
        ],
      ],
    ];
    $webhook_uuid = 'some-webhook-uuid';
    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('interest/webhook/' . $webhook_uuid)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->getInterestsByWebhook($webhook_uuid), $response['data']['interests']);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::addWebhook
   * @throws \Exception
   */
  public function testAddWebhookReturnsInfoAboutTheNewlyAddedWebhook(): void {
    $response = [
      'uuid' => 'some-webhook-uuid',
      'client_uuid' => 'some-client-uuid',
      'client_name' => 'some-client-name',
      'url' => 'new-webhook-url',
      'filters' => NULL,
      'status' => 'ENABLED',
    ];

    $arr = [
      'body' => json_encode([
        'url' => $response['url'],
        'version' => 2.0,
      ]),
    ];
    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with('settings/webhooks', $arr)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->addWebhook($response['url']), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::addWebhook
   * @throws \Exception
   */
  public function testAddingTheSameWebhookMoreThanOnceReturnsSameInfoAboutTheWebhook(): void {
    $response = [
      'uuid' => 'some-webhook-uuid',
      'client_uuid' => 'some-client-uuid',
      'client_name' => 'some-client-name',
      'url' => 'new-webhook-url',
      'filters' => NULL,
      'status' => 'ENABLED',
    ];

    $params = [
      'body' => json_encode([
        'url' => $response['url'],
        'version' => 2.0,
      ]),
    ];
    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with('settings/webhooks', $params)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $api_response_attempt_1 = $this->ch_client->addWebhook($response['url']);

    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with('settings/webhooks', $params)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $api_response_attempt_2 = $this->ch_client->addWebhook($response['url']);
    $this->assertSame($api_response_attempt_1, $api_response_attempt_2);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::deleteWebhook
   */
  public function testDeleteWebhookReturns200IfSuccessful(): void {
    $webhook_uuid = 'some-webhook-uuid';
    $response = json_encode([
      'success' => TRUE,
      'request_id' => 'some-request-uuid',
    ]);

    $this->ch_client
      ->shouldReceive('delete')
      ->once()
      ->with('settings/webhooks/' . $webhook_uuid)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], $response));

    $api_response = $this->ch_client->deleteWebhook($webhook_uuid);

    $this->assertSame($api_response->getStatusCode(), SymfonyResponse::HTTP_OK);
    $this->assertSame($api_response->getBody()->getContents(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::deleteWebhook
   */
  public function testDeleteWebhookReturns404IfNoSuchWebhookFound(): void {
    $webhook_uuid = 'some-non-existing-webhook-uuid';
    $response = json_encode([
      'success' => FALSE,
      'error' => [
        'code' => 4005,
        'message' => 'The requested webhook ID was not found.',
      ],
      'request_id' => 'some-request-uuid',
    ]);

    $this->ch_client
      ->shouldReceive('delete')
      ->once()
      ->with('settings/webhooks/' . $webhook_uuid)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_NOT_FOUND, [], $response));

    $api_response = $this->ch_client->deleteWebhook($webhook_uuid);

    $this->assertSame($api_response->getStatusCode(), SymfonyResponse::HTTP_NOT_FOUND);
    $this->assertSame($api_response->getBody()->getContents(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::updateWebhook
   */
  public function testUpdateWebhookFailsIfWebhookNotFound(): void {
    $webhook_uuid = 'some-non-existing-webhook-uuid';
    $new_webhook_data = [
      'version' => 1,
      'url' => 'some-valid-url',
      'disable_retries' => TRUE,
      'status' => 'DISABLED',
    ];

    $response = json_encode([
      'success' => FALSE,
      'error' => [
        'code' => 4004,
        'message' => 'Cannot find the webhook with the specified UUID.',
      ],
      'request_id' => 'some-request-uuid',
    ]);

    $this->ch_client
      ->shouldReceive('put')
      ->once()
      ->with('settings/webhooks/' . $webhook_uuid, ['body' => json_encode($new_webhook_data)])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_NOT_FOUND, [], $response));

    $api_response = $this->ch_client->updateWebhook($webhook_uuid, $new_webhook_data);

    $this->assertSame($api_response->getStatusCode(), SymfonyResponse::HTTP_NOT_FOUND);
    $this->assertSame($api_response->getBody()->getContents(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::updateWebhook
   */
  public function testUpdateWebhookFailsIfURLIsNotAcceptable(): void { // phpcs:ignore
    $webhook_uuid = 'some-existing-webhook-uuid';
    $new_webhook_data = [
      'version' => 1,
      'url' => 'some-unreachable-or-invalid-url',
      'disable_retries' => TRUE,
      'status' => 'DISABLED',
    ];

    $response = json_encode([
      'success' => FALSE,
      'error' => [
        'code' => 4005,
        'message' => 'The provided URL did not respond with a valid authorization. Current hmac version is 1.000000',
      ],
      'request_id' => 'some-request-uuid',
    ]);

    $this->ch_client
      ->shouldReceive('put')
      ->once()
      ->with('settings/webhooks/' . $webhook_uuid, ['body' => json_encode($new_webhook_data)])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_FORBIDDEN, [], $response));

    $api_response = $this->ch_client->updateWebhook($webhook_uuid, $new_webhook_data);

    $this->assertSame($api_response->getStatusCode(), SymfonyResponse::HTTP_FORBIDDEN);
    $this->assertSame($api_response->getBody()->getContents(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::updateWebhook
   */
  public function testUpdateWebhookSetsVersionTo2IfNot1Or2(): void {
    $webhook_uuid = 'some-existing-webhook-uuid';
    $new_webhook_data = [
      'version' => 100,
      'url' => 'some-valid-url',
      'disable_retries' => TRUE,
      'status' => 'ENABLED',
    ];

    $response = json_encode([
      'success' => TRUE,
      'request_id' => 'some-request-uuid',
      'data' => [
        'uuid' => 'some-webhook-uuid',
        'client_uuid' => 'some-client-uuid',
        'client_name' => 'some-client-name',
        'filters' => NULL,
        'url' => $new_webhook_data['url'],
        'status' => $new_webhook_data['status'],
        'version' => 2,
        'disable_retries' => $new_webhook_data['disable_retries'],
      ],
    ]);

    $this->ch_client
      ->shouldReceive('put')
      ->once()
      ->with('settings/webhooks/' . $webhook_uuid, [
        'body' => json_encode(array_merge($new_webhook_data, ['version' => 2])),
      ])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], $response));

    $api_response = $this->ch_client->updateWebhook($webhook_uuid, $new_webhook_data);

    $this->assertSame($api_response->getStatusCode(), SymfonyResponse::HTTP_OK);
    $this->assertSame($api_response->getBody()->getContents(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::suppressWebhook
   */
  public function testSuppressWebhook() {
    $webhook_uuid = 'some_uuid';
    $response = [
      'success' => TRUE,
      'request_id' => 'some-request-uuid',
    ];

    $this->ch_client
      ->shouldReceive('put')
      ->once()
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->suppressWebhook($webhook_uuid), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getWebhookStatus
   */
  public function testGetWebhookStatus() {
    $response = [
      'uuid' => 'UUID1',
      'status' => 200,
      'reason' => '',
    ];

    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->getWebhookStatus(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::unSuppressWebhook
   */
  public function testUnsuppressWebhook() {
    $webhook_uuid = 'some_uuid';
    $response = [
      'success' => TRUE,
      'request_id' => 'some-request-uuid',
    ];

    $this->ch_client
      ->shouldReceive('put')
      ->once()
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->unSuppressWebhook($webhook_uuid), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::addEntitiesToInterestList
   */
  public function testAddEntitiesToInterestListReturnsSuccess(): void {
    $webhook_uuid = 'some-webhook-uuid';
    $response = json_encode([
      'success' => TRUE,
      'request_id' => 'some-request-uuid',
    ]);

    $entity_uuids = [
      'entity-uuid-1',
      'entity-uuid-2',
    ];
    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with('interest/webhook/' . $webhook_uuid, ['body' => json_encode(['interests' => $entity_uuids])])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], $response));

    $api_response = $this->ch_client->addEntitiesToInterestList($webhook_uuid, $entity_uuids);

    $this->assertSame($api_response->getStatusCode(), SymfonyResponse::HTTP_OK);
    $this->assertSame($api_response->getBody()->getContents(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::deleteClient
   * @throws \Exception
   */
  public function testDeleteClientProceedsOnlyIfDeleteEntityIsSuccessful(): void {
    $client_uuid = 'some-client-uuid';

    $this->ch_client
      ->shouldReceive('delete')
      ->once()
      ->with('entities/' . $client_uuid)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_ACCEPTED, [], ''));

    $response = json_encode([
      'success' => TRUE,
      'request_id' => 'some-request-uuid',
    ]);
    $this->ch_client
      ->shouldReceive('delete')
      ->once()
      ->with('settings/client/uuid/' . $client_uuid)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], $response));

    $api_response = $this->ch_client->deleteClient($client_uuid);
    $this->assertSame($api_response->getStatusCode(), SymfonyResponse::HTTP_OK);
    $this->assertSame($api_response->getBody()->getContents(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::deleteClient
   * @throws \Exception
   */
  public function testDeleteClientThrowsExceptionIfAnythingGoesWrongWithDeleteEntity(): void {
    $client_uuid = 'some-client-uuid';

    $this->ch_client
      ->shouldReceive('delete')
      ->once()
      ->with('entities/' . $client_uuid)
      ->andReturn(FALSE);

    $this->expectException(\Exception::class);
    $this->ch_client->deleteClient($client_uuid);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::updateClient
   */
  public function testUpdateClientRejectsDuplicateClientName(): void {
    $client_uuid = 'some-uuid';
    $new_name = 'some-existing-client-name';

    $response = json_encode([
      'success' => FALSE,
      'error' => [
        'code' => 4007,
        'message' => 'A client with that name already exists.',
      ],
      'request_id' => 'some-request-uuid',
    ]);
    $this->ch_client
      ->shouldReceive('put')
      ->once()
      ->with('settings/client/uuid/' . $client_uuid, ['body' => json_encode(['name' => $new_name])])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_CONFLICT, [], $response));

    $api_response = $this->ch_client->updateClient($client_uuid, $new_name);
    $this->assertSame($api_response->getStatusCode(), SymfonyResponse::HTTP_CONFLICT);
    $this->assertSame($api_response->getBody()->getContents(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::updateClient
   */
  public function testUpdateClientRejectsNonExistingClient(): void {
    $client_uuid = 'some-non-existing-uuid';
    $new_name = 'some-unique-name';

    $response = json_encode([
      'success' => FALSE,
      'error' => [
        'code' => 4091,
        'message' => 'The requested client ID does not exist for the active subscription.',
      ],
      'request_id' => 'some-request-uuid',
    ]);
    $this->ch_client
      ->shouldReceive('put')
      ->once()
      ->with('settings/client/uuid/' . $client_uuid, ['body' => json_encode(['name' => $new_name])])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_NOT_FOUND, [], $response));

    $api_response = $this->ch_client->updateClient($client_uuid, $new_name);
    $this->assertSame($api_response->getStatusCode(), SymfonyResponse::HTTP_NOT_FOUND);
    $this->assertSame($api_response->getBody()->getContents(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::updateClient
   */
  public function testUpdateClientAcceptsAUniqueNameForAnExistingClient(): void { // phpcs:ignore
    $client_uuid = 'some-existing-uuid';
    $new_name = 'some-unique-name';

    $response = json_encode([
      'success' => TRUE,
      'request_id' => 'some-request-uuid',
    ]);
    $this->ch_client
      ->shouldReceive('put')
      ->once()
      ->with('settings/client/uuid/' . $client_uuid, ['body' => json_encode(['name' => $new_name])])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], $response));

    $api_response = $this->ch_client->updateClient($client_uuid, $new_name);
    $this->assertSame($api_response->getStatusCode(), SymfonyResponse::HTTP_OK);
    $this->assertSame($api_response->getBody()->getContents(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::regenerateSharedSecret
   * @throws \Exception
   */
  public function testRegenerateSharedSecretReturnsSuccessIfAllGoesWell(): void {
    $response = [
      'success' => TRUE,
      'request_id' => 'some-request-uuid',
    ];
    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with('settings/secret', ['body' => json_encode([])])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->regenerateSharedSecret(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getFilter
   * @throws \Exception
   */
  public function testGetFilterByUUIDFailsIfFilterNotExists(): void { // phpcs:ignore
    $filter_uuid = 'some-non-existing-uuid';
    $response = [
      'success' => FALSE,
      'error' => [
        'code' => 404,
        'message' => 'The provided filter ID does not exist for this subscription.',
      ],
      'request_id' => 'some-request-uuid',
    ];
    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('filters/' . $filter_uuid)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_NOT_FOUND, [], json_encode($response)));

    $this->assertSame($this->ch_client->getFilter($filter_uuid), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getFilter
   * @throws \Exception
   */
  public function testGetFilterByUUIDSucceedsIfFilterExists(): void {  // phpcs:ignore
    $filter_uuid = 'some-existing-uuid';
    $response = [
      'data' => [
        'uuid' => $filter_uuid,
        'name' => 'some-filter-name',
        'data' => [
          'query' => [
            'bool' => [
              'should' => [
                [
                  'match' => [
                    'data.attributes.channels.value.und' => 'some-data-attribute-channel-uuid',
                  ],
                ],
                [
                  'match' => [
                    'data.origin' => 'some-data-origin-id',
                  ],
                ],
              ],
            ],
          ],
        ],
        'real_time_filter' => FALSE,
        'metadata' => [],
        'version' => 2,
      ],
      'success' => TRUE,
      'request_id' => 'some-request-uuid',
    ];
    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('filters/' . $filter_uuid)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->getFilter($filter_uuid), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getFilterByName
   * @throws \Exception
   */
  public function testGetFilterByNameFailsIfFilterNotExists(): void {
    $filter_name = 'some-non-existing-name';
    $response = [
      'success' => FALSE,
      'error' => [
        'code' => 404,
        'message' => 'The provided filter name does not exist for this subscription.',
      ],
      'request_id' => 'some-request-uuid',
    ];
    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('filters?name=' . $filter_name)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_NOT_FOUND, [], json_encode($response)));

    $this->assertNull($this->ch_client->getFilterByName($filter_name));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getFilterByName
   * @throws \Exception
   */
  public function testGetFilterByNameSucceedsIfFilterExists(): void {
    $filter_name = 'some-existing-name';
    $response = [
      'data' => [
        'uuid' => 'some-filter-uuid',
        'name' => $filter_name,
        'data' => [
          'query' => [
            'bool' => [
              'should' => [
                [
                  'match' => [
                    'data.attributes.channels.value.und' => 'some-data-attribute-channel-uuid',
                  ],
                ],
                [
                  'match' => [
                    'data.origin' => 'some-data-origin-id',
                  ],
                ],
              ],
            ],
          ],
        ],
        'real_time_filter' => FALSE,
        'metadata' => [],
        'version' => 2,
      ],
      'success' => TRUE,
      'request_id' => 'some-request-uuid',
    ];
    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('filters?name=' . $filter_name)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->getFilterByName($filter_name), $response['data']);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::listFilters
   * @throws \Exception
   */
  public function testListFiltersReturnsArrayOfWebhooks(): void {
    $response = [
      'data' => [
        [
          'uuid' => 'filter-uuid-1',
          'name' => 'filter-name-1',
          'data' => [],
          'real_time_filter' => FALSE,
          'metadata' => [],
          'version' => 2,
        ],
        [
          'uuid' => 'filter-uuid-2',
          'name' => 'filter-name-2',
          'data' => [],
          'real_time_filter' => FALSE,
          'metadata' => [],
          'version' => 2,
        ],
      ],
      'success' => TRUE,
      'request_id' => 'some-request-id',
    ];

    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('filters')
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->listFilters(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::listFilters
   * @throws \Exception
   */
  public function testListFiltersReturnsAnArrayWithDataNullIfNoFilterExists(): void {
    $response = [
      'success' => TRUE,
      'request_id' => 'some-request-uuid',
      'data' => NULL,
    ];
    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with('filters')
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->listFilters(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::putFilter
   * @throws \Exception
   */
  public function testPutFilterAddsFilterToTheClientIfNameNotAlreadyExists(): void {
    $filter_uuid = 'some-filter-uuid';
    $filter_name = 'some-unique-filter-name';
    $filter_metadata = [];
    $query = [
      'bool' => [
        'should' => [
          [
            'match' => [
              'data.type' => 'some-type',
            ],
          ],
        ],
      ],
    ];

    $response = [
      'uuid' => 'some-uuid',
      'request_id' => 'some-request-id',
      'success' => TRUE,
    ];

    $request_parameters = [
      'name' => $filter_name,
      'data' => [
        'query' => $query,
      ],
      'metadata' => (object) $filter_metadata,
      'uuid' => $filter_uuid,
    ];

    $this->ch_client
      ->shouldReceive('put')
      ->once()
      ->with('filters', ['body' => json_encode($request_parameters)])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->putFilter($query, $filter_name, $filter_uuid), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::putFilter
   * @throws \Exception
   */
  public function testPutFilterErrorsOutIfNameAlreadyExists(): void {
    $filter_uuid = 'some-filter-uuid';
    $filter_name = 'some-existing-filter-name';
    $filter_metadata = [];
    $query = [
      'bool' => [
        'should' => [
          [
            'match' => [
              'data.type' => 'some-type',
            ],
          ],
        ],
      ],
    ];

    $response = [
      'error' => [
        'code' => 400,
        'message' => 'Filter is already existing with the given name.',
      ],
      'request_id' => 'some-request-id',
      'success' => FALSE,
    ];

    $request_parameters = [
      'name' => $filter_name,
      'data' => [
        'query' => $query,
      ],
      'metadata' => (object) $filter_metadata,
      'uuid' => $filter_uuid,
    ];

    $this->ch_client
      ->shouldReceive('put')
      ->once()
      ->with('filters', ['body' => json_encode($request_parameters)])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_BAD_REQUEST, [], json_encode($response)));

    $this->assertSame($this->ch_client->putFilter($query, $filter_name, $filter_uuid), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::deleteFilter
   */
  public function testDeleteFilterReturnsAnArrayOfExistingFilters(): void {
    $filter_uuid = 'some-uuid';
    $response = json_encode([
      'success' => TRUE,
      'request_id' => 'some-request-uuid',
    ]);
    $this->ch_client
      ->shouldReceive('delete')
      ->once()
      ->with('filters/' . $filter_uuid)
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], $response));

    $api_response = $this->ch_client->deleteFilter($filter_uuid);
    $this->assertSame($api_response->getStatusCode(), SymfonyResponse::HTTP_OK);
    $this->assertSame($api_response->getBody()->getContents(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::listFiltersForWebhook
   * @throws \Exception
   */
  public function testListFiltersForWebhookSucceedsIfWebhookExists(): void {
    $webhook_uuid = 'some-existing-webhook-uuid';
    $response = [
      'data' => [
        'filter-1-uuid',
        'filter-2-uuid',
      ],
      'request_id' => 'some-request-uuid',
      'success' => TRUE,
    ];

    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with("settings/webhooks/${webhook_uuid}/filters")
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->listFiltersForWebhook($webhook_uuid), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::addFilterToWebhook
   * @throws \Exception
   */
  public function testAddFilterToWebhookSucceedsIfAllGoesWell(): void {
    $filter_id = 'some-existing-filter-uuid';
    $webhook_id = 'some-existing-webhook-uuid';
    $response = [
      'request_id' => 'some-request-uuid',
      'success' => TRUE,
    ];

    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with("settings/webhooks/${webhook_id}/filters", ['body' => json_encode(['filter_id' => $filter_id])])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->addFilterToWebhook($filter_id, $webhook_id), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::addFilterToWebhook
   * @throws \Exception
   */
  public function testAddFilterToWebhookFailsIfFilterNotExists(): void {
    $filter_id = 'some-non-existing-filter-uuid';
    $webhook_id = 'some-existing-webhook-uuid';
    $response = [
      'request_id' => 'some-request-uuid',
      'error' => [
        'code' => 4004,
        'message' => 'Non-existent filter.',
      ],
      'success' => FALSE,
    ];

    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with("settings/webhooks/${webhook_id}/filters", ['body' => json_encode(['filter_id' => $filter_id])])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_NOT_FOUND, [], json_encode($response)));

    $this->assertSame($this->ch_client->addFilterToWebhook($filter_id, $webhook_id), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::addFilterToWebhook
   * @throws \Exception
   */
  public function testAddFilterToWebhookFailsIfWebhookNotExists(): void {
    $filter_id = 'some-non-existing-filter-uuid';
    $webhook_id = 'some-existing-webhook-uuid';
    $response = [
      'request_id' => 'some-request-uuid',
      'error' => [
        'code' => 4004,
        'message' => 'Webhook with the given uuid is not found.',
      ],
      'success' => FALSE,
    ];

    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with("settings/webhooks/${webhook_id}/filters", ['body' => json_encode(['filter_id' => $filter_id])])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_NOT_FOUND, [], json_encode($response)));

    $this->assertSame($this->ch_client->addFilterToWebhook($filter_id, $webhook_id), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::removeFilterFromWebhook
   * @throws \Exception
   */
  public function testRemoveFilterFromWebhookFailsIfWebhookNotExists(): void {
    $filter_id = 'some-existing-filter-uuid';
    $webhook_id = 'some-non-existing-webhook-uuid';
    $response = [
      'request_id' => 'some-request-uuid',
      'error' => [
        'code' => 4004,
        'message' => 'Webhook with the given uuid is not found.',
      ],
      'success' => FALSE,
    ];

    $this->ch_client
      ->shouldReceive('delete')
      ->once()
      ->with("settings/webhooks/${webhook_id}/filters", ['body' => json_encode(['filter_id' => $filter_id])])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_NOT_FOUND, [], json_encode($response)));

    $this->assertSame($this->ch_client->removeFilterFromWebhook($filter_id, $webhook_id), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::removeFilterFromWebhook
   * @throws \Exception
   */
  public function testRemoveFilterFromWebhookSucceedsEvenIfFilterNotExists(): void {
    $filter_id = 'some-non-existing-filter-uuid';
    $webhook_id = 'some-existing-webhook-uuid';
    $response = [
      'success' => TRUE,
      'request_id' => 'some-request-uuid',
    ];

    $this->ch_client
      ->shouldReceive('delete')
      ->once()
      ->with("settings/webhooks/${webhook_id}/filters", ['body' => json_encode(['filter_id' => $filter_id])])
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->removeFilterFromWebhook($filter_id, $webhook_id), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getResponseJson
   * @throws \Exception
   */
  public function testGetResponseJsonReturnsJSONDecodedResponse(): void {  // phpcs:ignore
    $response_body_array = [1, 2, 3];
    $mocked_response = $this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response_body_array));
    $this->assertSame($this->ch_client::getResponseJson($mocked_response), $response_body_array);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getResponseJson
   * @throws \Exception
   */
  public function testGetResponseJsonThrowsExceptionIfAnythingFails(): void {
    $response = \Mockery::mock(Response::class);
    $response
      ->shouldReceive('getBody')
      ->andThrow(new \Exception());

    $this->expectException(\Exception::class);

    $this->ch_client::getResponseJson($response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::addSearchCriteriaHeader
   */
  public function testAddSearchCriteriaHeaderKeepsTheInputIntactIfNoQuestionMarkIsPresentInTheEndpointPath(): void {
    $input = ['path/to/some/endpoint/without/any/question-mark'];

    $this->assertSame($this->ch_client->addSearchCriteriaHeader($input), $input);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::addSearchCriteriaHeader
   */
  public function testAddSearchCriteriaHeaderKeepsTheInputIntactIfNothingIsPresentInTheEndpointPathAfterTheQuestionMark(): void {
    $input = ['path/to/some/endpoint/with/question-mark/but-nothing-after-it?'];

    $this->assertSame($this->ch_client->addSearchCriteriaHeader($input), $input);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::addSearchCriteriaHeader
   */
  public function testAddSearchCriteriaAddsLanguagesToQueryStringIfAny(): void {
    $input = ['path/to/some/endpoint/with/question-mark?a=b&c=d'];
    $output = $this->ch_client->addSearchCriteriaHeader($input);

    $this->assertTrue(isset($output[1]['headers'][SearchCriteria::HEADER_NAME]));
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getErrorResponse
   */
  public function testGetErrorResponseReturnsResponse(): void {
    $code = SymfonyResponse::HTTP_FORBIDDEN;
    $reason = 'some-reason';

    $response = $this->ch_client->getErrorResponse($code, $reason);
    $this->assertSame($response->getStatusCode(), $code);
    $this->assertSame($response->getReasonPhrase(), $reason);

    $response = $this->ch_client->getErrorResponse(0, $reason);
    $this->assertSame($response->getStatusCode(), 500);
    $this->assertSame($response->getReasonPhrase(), $reason);
  }

  /**
   * Mock response.
   *
   * @param int $status
   *   Response status code.
   * @param array $headers
   *   Headers.
   * @param string $body
   *   Response body.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Mocked object.
   */
  private function makeMockResponse(
    int $status,
    array $headers,
    string $body
  ): ResponseInterface {
    $response = \Mockery::mock(Response::class);

    $response->shouldReceive('getStatusCode')->andReturn($status);
    $response->shouldReceive('getHeaders')->andReturn($headers);
    $response->shouldReceive('getBody')
      ->andReturn(\GuzzleHttp\Psr7\stream_for($body));

    return $response;
  }

  /**
   * Mock settings.
   *
   * @param string $name
   *   Name.
   * @param string $uuid
   *   UUID.
   * @param string $api_key
   *   API key.
   * @param string $secret
   *   API secret.
   * @param string $url
   *   URL.
   * @param string|null $shared_secret
   *   Shared secret.
   * @param array $webhook
   *   Webhook definition.
   *
   * @return \Acquia\ContentHubClient\Settings
   *   Mocked object.
   */
  private function makeMockSettings(
    string $name,
    string $uuid,
    string $api_key,
    string $secret,
    string $url,
    ?string $shared_secret = NULL,
    array $webhook = []
  ): Settings {
    $settings = \Mockery::mock(Settings::class);

    $settings->shouldReceive('getName')->andReturn($name);
    $settings->shouldReceive('getUuid')->andReturn($uuid);
    $settings->shouldReceive('getApiKey')->andReturn($api_key);
    $settings->shouldReceive('getSecretKey')->andReturn($secret);
    $settings->shouldReceive('getUrl')->andReturn($url);
    $settings->shouldReceive('getSharedSecret')->andReturn($shared_secret);
    $settings->shouldReceive('getMiddleware')
      ->andReturn(\Mockery::mock(HmacAuthMiddleware::class));

    return $settings;
  }

  /**
   * Mock CH client.
   *
   * @param array $config
   *   Config.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   * @param \Acquia\ContentHubClient\Settings $settings
   *   Settings.
   * @param \Acquia\Hmac\Guzzle\HmacAuthMiddleware $middleware
   *   Middleware.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event dispatcher.
   * @param string $api_version
   *   API version.
   *
   * @return \Acquia\ContentHubClient\ContentHubClient
   *   Mocked object.
   *
   * @throws \ReflectionException
   */
  public function makeMockCHClient( // phpcs:ignore
    array $config,
    LoggerInterface $logger,
    Settings $settings,
    HmacAuthMiddleware $middleware,
    EventDispatcherInterface $dispatcher,
    string $api_version = 'v2'
  ): ContentHubClient {
    $client = \Mockery::mock(ContentHubClient::class)
      ->makePartial()
      ->shouldAllowMockingProtectedMethods();

    self::mockProperty($client, 'dispatcher', $dispatcher);

    $client
      ->shouldReceive('getConfig')
      ->andReturnUsing(static function ($key = NULL) use ($config) {
        if (NULL === $key) {
          return $config;
        }

        return $config[$key] ?? NULL;
      });

    $client->shouldReceive('getSettings')->andReturn($settings);
    $client->shouldReceive('getRemoteSettings')->andReturn([
      'hostname' => $this->test_data['host-name'],
      'api_key' => $this->test_data['api-key'],
      'secret_key' => $this->test_data['secret-key'],
      'shared_secret' => $settings->getSharedSecret() ?? $this->test_data['shared-secret'],
      'client_name' => $settings->getName(),
      'clients' => $this->test_data['clients'],
      'webhooks' => [
        [
          'uuid' => 'some-webhook-uuid',
          'client_uuid' => 'some-client-id',
          'client_name' => 'some-client-name',
          'url' => 'some-webhook-url',
          'version' => 2,
          'disable_retries' => FALSE,
          'filters' => [
            'filter-1-uuid',
          ],
          'status' => 'ENABLED',
          'is_migrated' => FALSE,
          'suppressed_until' => 'some-timestamp',
        ],
      ],
    ]);

    return $client;
  }

  /**
   * Make registration request.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   *
   * @return \Acquia\ContentHubClient\ContentHubClient
   *   ContentHubClient instance.
   *
   * @throws \Exception
   */
  private function makeRegistrationRequest(LoggerInterface $logger): ContentHubClient {
    return $this->ch_client::register(
      $logger,
      \Mockery::mock(EventDispatcher::class),
      $this->test_data['name'],
      $this->test_data['url'],
      $this->test_data['api-key'],
      $this->test_data['secret-key'],
      $this->test_data['api-version']
    );
  }

  /**
   * Tests Guzzle config.
   */
  private function assertGuzzleConfig(): void {
    $config = $this->guzzle_client->getConfig();

    $this->assertSame($config['headers']['Content-Type'], 'application/json');
    $this->assertSame($config['base_uri'], $this->test_data['uri']);
  }

  /**
   * Tests response items.
   *
   * @param \Acquia\ContentHubClient\ContentHubClient $response
   *   ContentHubClient instance.
   */
  private function assertResponseItems(ContentHubClient $response): void {
    $settings = $response->getSettings();

    $this->assertSame($response->getConfig()['base_url'], $settings->getUrl());
    $this->assertSame($settings->getUrl(), $this->test_data['url']);
    $this->assertSame($settings->getName(), $this->test_data['name']);
    $this->assertSame($settings->getUuid(), $this->test_data['client-uuid']);
    $this->assertSame($settings->getSharedSecret(), $this->test_data['shared-secret']);
  }

  /**
   * Mock logger.
   *
   * @param string $method
   *   Method name.
   *
   * @return \Psr\Log\LoggerInterface
   *   Mocked object.
   */
  private function getMockLogger($method = 'log'): LoggerInterface {
    $logger = \Mockery::mock(LoggerInterface::class);
    $logger->shouldReceive($method)->once();

    return $logger;
  }

  /**
   * Mock CDFDocument.
   *
   * phpcs:ignore @param \Acquia\ContentHubClient\CDF\CDFObject ...$entities
   *   CDF objects.
   *
   * @return \Acquia\ContentHubClient\CDFDocument
   *   Mocked object.
   */
  private function makeMockCDFDocument(CDFObject ...$entities): CDFDocument { // phpcs:ignore
    $cdf_document = \Mockery::mock(CDFDocument::class);

    $cdf_document->shouldReceive('getEntities')->andReturn($entities);

    return $cdf_document;
  }

  /**
   * Mock event dispatcher.
   *
   * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
   *   Mocked object.
   */
  private function getMockDispatcher(): EventDispatcherInterface {
    return \Mockery::mock(EventDispatcher::class);
  }

  /**
   * Mock GetCDFTypeEvent.
   *
   * @param array $cdf_array
   *   Data.
   *
   * @return \Acquia\ContentHubClient\Event\GetCDFTypeEvent
   *   Mocked object.
   */
  private function makeMockCdfTypeEvent(array $cdf_array): GetCDFTypeEvent {
    $cdf = \Mockery::mock(CDFObject::class);
    $cdf->shouldReceive('toArray')->andReturn($cdf_array);

    $cdf_type_event = \Mockery::mock(GetCDFTypeEvent::class);

    $cdf_type_event->shouldReceive('getObject')->andReturn($cdf);

    return $cdf_type_event;
  }

  /**
   * Mock webhook.
   *
   * @param array $definition
   *   Webhook definition.
   *
   * @return \Acquia\ContentHubClient\Webhook|\Mockery\LegacyMockInterface|\Mockery\MockInterface
   *   Mocked object.
   */
  private function makeMockWebhook(array $definition) {
    $webhook = \Mockery::mock(Webhook::class);

    $webhook->shouldReceive('getDefinition')->andReturn($definition);

    return $webhook;
  }

  /**
   * Mock property.
   *
   * @param mixed $object
   *   Object.
   * @param string $property_name
   *   Property name.
   * @param mixed $value
   *   Property value.
   *
   * @throws \ReflectionException
   */
  private static function mockProperty($object, string $property_name, $value): void {
    $reflectionClass = new \ReflectionClass($object);

    $property = $reflectionClass->getProperty($property_name);
    $property->setAccessible(TRUE);
    $property->setValue($object, $value);
    $property->setAccessible(FALSE);
  }

  /**
   * Returns test search result.
   *
   * @param int $id
   *   Request ID.
   * @param string $type
   *   Search type.
   *
   * @return array
   *   Test data.
   */
  private function getElasticSearchItemWithId(int $id = 1, string $type = 'some-type'): array {
    return [
      '_id' => $id,
      '_index' => 'some-index',
      '_score' => 1,
      '_type' => $type,
      '_source' => [
        'data' => [
          'type' => 'some-type',
        ],
      ],
    ];
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::createSnapshot
   * @throws \Exception
   */
  public function testCreateSnapshotIfSucceeds(): void {
    $response = [
      'request_id' => 'some-request-uuid',
      'success' => TRUE,
      'data' => 'some-data',
    ];

    $this->ch_client
      ->shouldReceive('post')
      ->once()
      ->with("snapshots")
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->createSnapshot(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::getSnapshots
   * @throws \Exception
   */
  public function testGetSnapshotIfSucceeds(): void {
    $response = [
      'request_id' => 'some-request-uuid',
      'success' => TRUE,
      'data' => [
        'some-data',
      ]
    ];

    $this->ch_client
      ->shouldReceive('get')
      ->once()
      ->with("snapshots")
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->getSnapshots(), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::deleteSnapshot
   * @throws \Exception
   */
  public function testDeleteSnapshotIfSucceeds(): void {
    $snapshot = 'some-snapshot';
    $response = [
      'request_id' => 'some-request-uuid',
      'success' => TRUE,
      'data' => 'some-data',
    ];

    $this->ch_client
      ->shouldReceive('delete')
      ->once()
      ->with("snapshots/$snapshot")
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->deleteSnapshot($snapshot), $response);
  }

  /**
   * @covers \Acquia\ContentHubClient\ContentHubClient::restoreSnapshot
   * @throws \Exception
   */
  public function testRestoreSnapshotIfSucceeds(): void {
    $snapshot = 'some-snapshot';
    $response = [
      'request_id' => 'some-request-uuid',
      'success' => TRUE,
      'data' => 'some-data',
    ];

    $this->ch_client
      ->shouldReceive('put')
      ->once()
      ->with("snapshots/$snapshot/restore")
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response)));

    $this->assertSame($this->ch_client->restoreSnapshot($snapshot), $response);
  }

}
