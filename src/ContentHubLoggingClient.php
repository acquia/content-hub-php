<?php

namespace Acquia\ContentHubClient;

use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ContentHubLoggingClient.
 *
 * @package Acquia\ContentHubClient
 */
final class ContentHubLoggingClient implements ContentHubLoggingClientInterface {

  use ContentHubClientTrait;

  /**
   * The settings.
   *
   * @var \Acquia\ContentHubClient\Settings
   */
  protected $settings;

  /**
   * The Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  // phpcs:disable
  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    LoggerInterface $logger,
    Settings $settings,
    HmacAuthMiddleware $middleware,
    EventDispatcherInterface $dispatcher,
    array $config = []
  ) {
    $this->logger = $logger;
    $this->settings = $settings;
    $this->dispatcher = $dispatcher;

    // "base_url" parameter changed to "base_uri" in Guzzle6, so the following line
    // is there to make sure it does not disrupt previous configuration.
    if (!isset($config['base_uri']) && isset($config['base_url'])) {
      $config['base_uri'] = self::makeBaseURL($config['base_url']);
    }
    else {
      $config['base_uri'] = self::makeBaseURL($config['base_uri']);
    }

    // Setting up the User Header string.
    $user_agent_string = ContentHubDescriptor::userAgent();
    if (isset($config['client-user-agent'])) {
      $user_agent_string = $config['client-user-agent'] . ' ' . $user_agent_string;
    }

    // Setting up the headers.
    $config['headers']['Content-Type'] = 'application/json';
    $config['headers']['X-Acquia-Plexus-Client-Id'] = $settings->getUuid();
    $config['headers']['User-Agent'] = $user_agent_string;

    // Add the authentication handler.
    // @see https://github.com/acquia/http-hmac-spec
    if (!isset($config['handler'])) {
      $config['handler'] = ObjectFactory::getHandlerStack();
    }
    $config['handler']->push($middleware);
    $this->addRequestResponseHandler($config);

    $this->httpClient = ObjectFactory::getGuzzleClient($config);
    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function sendLogs(array $logs): array {
    $options['body'] = json_encode($logs, JSON_THROW_ON_ERROR);
    return self::getResponseJson($this->post('events', $options)) ?? [];
  }

}
