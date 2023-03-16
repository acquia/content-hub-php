<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\ContentHubLoggingClient;
use Acquia\ContentHubClient\Settings;
use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
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

  use ProphecyTrait;

  /**
   * CH client.
   *
   * @var \Acquia\ContentHubClient\ContentHubLoggingClient
   */
  private $ch_client; // phpcs:ignore

  /**
   * Test data.
   *
   * @var array
   */
  private $test_data; // phpcs:ignore

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
    ];

    $this->dispatcher = $this->prophesize(EventDispatcher::class);

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
      $this->prophesize(HmacAuthMiddleware::class)->reveal(),
      $this->dispatcher->reveal()
    );

    $this->test_data['uri'] = $this->ch_client::makeBaseURL($this->test_data['url']);
  }

  /**
   * Tests sends event log to events micro service.
   *
   * @covers \Acquia\ContentHubClient\ContentHubLoggingClient::sendLogs
   *
   * @throws \Exception
   */
  public function testSendLogs(): void {
    $response_body = [
      'success' => TRUE,
      'request_id' => 'some-uuid',
    ];

    $context = [
      'object_id' => 'object_id',
      'event_name' => 'event_name',
      'object_type' => 'object_type',
      'severity' => 'ERROR',
      'content' => 'some message',
      'origin' => 'some-origin',
    ];

    $this->ch_client
      ->shouldReceive('post')
      ->andReturn($this->makeMockResponse(SymfonyResponse::HTTP_OK, [], json_encode($response_body)));

    $send_log = $this->ch_client->sendLogs($context);
    $this->assertSame($send_log, $response_body);
  }

  /**
   * Tests if the config is set and available during execution.
   */
  public function testConfig(): void {
    $base_url = $this->ch_client->getConfig('base_url');
    $this->assertEquals('some-host-name', $base_url, 'Configuration is available during runtime.');
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
    $this->settings = $this->prophesize(Settings::class);

    $this->settings->getName()->willReturn($name);
    $this->settings->getUuid()->willReturn($uuid);
    $this->settings->getApiKey()->willReturn($api_key);
    $this->settings->getSecretKey()->willReturn($secret);
    $this->settings->getUrl()->willReturn($url);
    $this->settings->getSharedSecret()->willReturn($shared_secret);
    $this->settings->getMiddleware()
      ->willReturn($this->prophesize(HmacAuthMiddleware::class)->reveal());

    return $this->settings->reveal();
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
    $response->getBody()->willReturn(Utils::streamFor($body));

    return $response->reveal();
  }

}
