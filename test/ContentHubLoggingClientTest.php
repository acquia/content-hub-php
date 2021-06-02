<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\CDFDocument;
use Acquia\ContentHubClient\ContentHubLoggingClient;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use Acquia\ContentHubClient\ObjectFactory;
use Acquia\ContentHubClient\Settings;
use Acquia\ContentHubClient\Webhook;
use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * @covers \Acquia\ContentHubClient\ContentHubLoggingClient
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ContentHubLoggingClientTest extends TestCase {

  /**
   * CH client.
   *
   * @var \Acquia\ContentHubClient\ContentHubLoggingClient
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
   * Settings Instance.
   *
   * @var \Acquia\ContentHubClient\Settings
   */
  protected $settings;

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

    $handler_stack = \Mockery::mock(HandlerStack::class);
    $mock_hmac_middleware = \Mockery::mock(HmacAuthMiddleware::class);

    $this->guzzle_client = \Mockery::mock(Client::class);
    $this->object_factory = \Mockery::mock('alias:' . ObjectFactory::class);
    $this->dispatcher = \Mockery::mock(EventDispatcher::class);

    $this->settings = $this->makeMockSettings(
      $this->test_data['name'],
      $this->test_data['uuid'],
      $this->test_data['api-key'],
      $this->test_data['secret-key'],
      $this->test_data['url'],
      $this->test_data['shared-secret'],
      []
    );

    $this->ch_client = $this->makeMockCHLoggingClient(
      [
        'base_url' => $this->test_data['url'],
      ],
      new NullLogger(),
      $this->settings,
      \Mockery::mock(HmacAuthMiddleware::class),
      $this->dispatcher
    );

    $this->test_data['uri'] = $this->ch_client::makeBaseURL($this->test_data['url']);

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
        EventDispatcherInterface $dispatcher
      ) {
        return $this->makeMockCHLoggingClient($config, $logger, $settings, $middleware,
          $dispatcher);
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
   * Tests sends event log to events micro service.
   *
   * @covers \Acquia\ContentHubClient\ContentHubLoggingClient::sendLog
   *
   * @throws \Exception
   */
  public function testSendLong(): void {
    $response_body = [
      'success' => TRUE,
      'request_id' => 'some-uuid',
    ];

    $context = [
      'object_id' => 'object_id',
      'event_name' => 'event_name',
      'object_type' => 'object_type',
      'relevant_score' => 5,
      'status' => 'Error',
      'content' => json_encode(['message' => 'some message']),
    ];
    $context['origin'] = $this->ch_client->getSettings()->getUuid();

    $this->ch_client
      ->shouldReceive('post')
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response_body)));

    $send_log = $this->ch_client->sendLog('Error', 'Message', $context);
    $this->assertSame($send_log, $response_body);
  }

  /**
   * Tests get context array method.
   *
   * @covers \Acquia\ContentHubClient\ContentHubLoggingClient::getContextArray
   */
  public function testGetContextArray(): void {
    $status = 'status';
    $message = 'message';
    $context = [
      'object_id' => 'object_id',
      'event_name' => 'event_name',
      'object_type' => 'object_type',
      'relevant_score' => 5,
    ];
    $actual_outcome = $this->ch_client->getContextArray($status, $message, $context);

    $expected_outcome = $context + [
      'status' => $status,
      'content' => json_encode(['message' => $message]),
    ];

    $this->assertSame($expected_outcome, $actual_outcome);
  }

  /**
   * Tests get context array exception.
   *
   * @covers \Acquia\ContentHubClient\ContentHubLoggingClient::getContextArray
   *
   * @throws \Exception
   */
  public function testGetContextArrayException(): void {
    $status = 'status';
    $message = 'message';
    $context = [
      'object_id' => 'object_id',
      'event_name' => 'event_name',
      'object_type' => 'object_type',
    ];

    $this->expectExceptionMessage('Object Id(UUID) / Event Name/ Object Type/ Relevant score missing from event log attributes');
    $this->ch_client->getContextArray($status, $message, $context);
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
   * Mock Content Hub Logging Client.
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
   *
   * @return \Acquia\ContentHubClient\ContentHubLoggingClient
   *   Mocked object.
   *
   * @throws \ReflectionException
   */
  public function makeMockCHLoggingClient( // phpcs:ignore
    array $config,
    LoggerInterface $logger,
    Settings $settings,
    HmacAuthMiddleware $middleware,
    EventDispatcherInterface $dispatcher
  ): ContentHubLoggingClient {
    $client = \Mockery::mock(ContentHubLoggingClient::class)
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
    $this->settings = \Mockery::mock(Settings::class);

    $this->settings->shouldReceive('getName')->andReturn($name);
    $this->settings->shouldReceive('getUuid')->andReturn($uuid);
    $this->settings->shouldReceive('getApiKey')->andReturn($api_key);
    $this->settings->shouldReceive('getSecretKey')->andReturn($secret);
    $this->settings->shouldReceive('getUrl')->andReturn($url);
    $this->settings->shouldReceive('getSharedSecret')->andReturn($shared_secret);
    $this->settings->shouldReceive('getMiddleware')
      ->andReturn(\Mockery::mock(HmacAuthMiddleware::class));

    return $this->settings;
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
    $response = $this->prophesize(Response::class);

    $response->getStatusCode()->willReturn($status);
    $response->getHeaders()->willReturn($headers);
    $response->getBody()->willReturn(\GuzzleHttp\Psr7\stream_for($body));

    return $response->reveal();
  }

}
