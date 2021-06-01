<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\ContentHubLoggingClient;
use Acquia\ContentHubClient\ObjectFactory;
use Acquia\ContentHubClient\Settings;
use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use GuzzleHttp\Client;
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
   * Content Hub Logging Client class.
   *
   * @var \Acquia\ContentHubClient\ContentHubLoggingClient
   */
  protected $logginClientClass;

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
      'host-name' => 'some-host-name',
      'shared-secret' => 'some-shared-secret',
    ];

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
      $this->dispatcher,
    );

    $this->object_factory->shouldReceive('getGuzzleClient')
      ->andReturnUsing(function (array $config) {
        $this->guzzle_client->shouldReceive('getConfig')->andReturn($config);
        return $this->guzzle_client;
      });
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

    $this->guzzle_client
      ->shouldReceive('post')
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response_body)));

    $send_log = $this->ch_client->sendLog('Error', 'Message', $context);
    $this->assertSame($send_log, $response_body);
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

    $client
      ->shouldReceive('getConfig')
      ->andReturnUsing(static function ($key = NULL) use ($config) {
        if (NULL === $key) {
          return $config;
        }
        return $config[$key] ?? NULL;
      });
    $client->shouldReceive('getSettings')->andReturn($settings);

    return $client;
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
