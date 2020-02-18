<?php

namespace Acquia\ContentHubClient;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ObjectFactory.
 *
 * @package Acquia\ContentHubClient
 * @codeCoverageIgnore
 */
class ObjectFactory {

  /**
   * Creates Guzzle client.
   *
   * @param array $config
   *   Initial data.
   *
   * @return \GuzzleHttp\Client
   *   GuzzleClient instance.
   */
  public static function getGuzzleClient(array $config): Client {
    return new Client($config);
  }

  /**
   * Creates authentication key.
   *
   * @param string $api_key
   *   API key.
   * @param string $secret
   *   Secret.
   *
   * @return \Acquia\Hmac\Key
   *   Key instance.
   */
  public static function getAuthenticationKey($api_key, $secret): Key {
    return new Key($api_key, $secret);
  }

  /**
   * Creates HmacAuthMiddleware.
   *
   * @param \Acquia\Hmac\Key $key
   *   Key instance.
   *
   * @return \Acquia\Hmac\Guzzle\HmacAuthMiddleware
   *   HmacAuthMiddleware instance.
   */
  public static function getHmacAuthMiddleware(Key $key): HmacAuthMiddleware {
    return new HmacAuthMiddleware($key);
  }

  /**
   * Creates Settings object instance.
   *
   * @param string $name
   *   Name of settings group.
   * @param string $uuid
   *   The assigned UUID.
   * @param string $api_key
   *   API key.
   * @param string $secret
   *   Secret key.
   * @param string $url
   *   The URL of the end point to consult.
   * @param string|null $shared_secret
   *   Shared secret.
   * @param array $webhook
   *   Webhook UUID.
   *
   * @return \Acquia\ContentHubClient\Settings
   *   Settings object instance.
   */
  public static function instantiateSettings(string $name, string $uuid, string $api_key, string $secret, string $url, ?string $shared_secret = NULL, array $webhook = []): Settings {
    return new Settings($name, $uuid, $api_key, $secret, $url, $shared_secret,
      $webhook);
  }

  /**
   * Creates a default handler stack that can be used by clients.
   *
   * @return \GuzzleHttp\HandlerStack
   *   HandlerStack instance.
   */
  public static function getHandlerStack(): HandlerStack {
    return HandlerStack::create();
  }

  /**
   * Creates ContentHubClient instance.
   *
   * @param array $config
   *   Config.
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger instance.
   * @param \Acquia\ContentHubClient\Settings $settings
   *   Settings instance.
   * @param \Acquia\Hmac\Guzzle\HmacAuthMiddleware $middleware
   *   HmacAuthMiddleware instance.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event dispatcher instance.
   * @param string $api_version
   *   API version.
   *
   * @return \Acquia\ContentHubClient\ContentHubClient
   *   ContentHubClient instance.
   */
  public static function getCHClient( // phpcs:ignore
    array $config,
    LoggerInterface $logger,
    Settings $settings,
    HmacAuthMiddleware $middleware,
    EventDispatcherInterface $dispatcher,
    string $api_version = 'v2'
  ): ContentHubClient {
    return new ContentHubClient($config, $logger, $settings, $middleware,
      $dispatcher, $api_version);
  }

  /**
   * Returns GetCDFTypeEvent instance.
   *
   * @param array $data
   *   Event payload.
   *
   * @return \Acquia\ContentHubClient\Event\GetCDFTypeEvent
   *   GetCDFTypeEvent instance.
   */
  public static function getCDFTypeEvent(array $data): GetCDFTypeEvent { // phpcs:ignore
    return new GetCDFTypeEvent($data);
  }

  /**
   * Returns CDFDocument.
   *
   * phpcs:disable
   * @param \Acquia\ContentHubClient\CDF\CDFObject ...$entities
   * phpcs:enable
   *   Array of CDFObjects.
   *
   * @return \Acquia\ContentHubClient\CDFDocument
   *   CDFDocument.
   */
  public static function getCDFDocument(CDFObject ...$entities): CDFDocument { // phpcs:ignore
    return new CDFDocument(...$entities);
  }

  /**
   * Creates webhook from definition.
   *
   * @param array $definition
   *   Webhook definition.
   *
   * @return \Acquia\ContentHubClient\Webhook
   *   Webhook instance.
   */
  public static function getWebhook(array $definition): Webhook {
    return new Webhook($definition);
  }

}
