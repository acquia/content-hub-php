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
 * @codeCoverageIgnore
 */

class ObjectFactory {

  /**
   * @param array $config
   *
   * @return Client
   */
  public static function getGuzzleClient(array $config): Client {
    return new Client($config);
  }

  /**
   * @param $api_key
   * @param $secret
   *
   * @return \Acquia\Hmac\Key
   */
  public static function getAuthenticationKey($api_key, $secret): Key {
    return new Key($api_key, $secret);
  }

  /**
   * @param \Acquia\Hmac\Key $key
   *
   * @return \Acquia\Hmac\Guzzle\HmacAuthMiddleware
   */
  public static function getHmacAuthMiddleware(Key $key): HmacAuthMiddleware {
    return new HmacAuthMiddleware($key);
  }

  /**
   * @param string $name
   * @param string $uuid
   * @param string $api_key
   * @param string $secret
   *
   * @param string $url
   * @param string|null $shared_secret
   * @param array $webhook
   *
   * @return \Acquia\ContentHubClient\Settings
   */
  public static function instantiateSettings(string $name, string $uuid, string $api_key, string $secret, string $url, ?string $shared_secret = NULL, array $webhook = []): Settings {
    return new Settings($name, $uuid, $api_key, $secret, $url, $shared_secret, $webhook);
  }

  /**
   * @return \GuzzleHttp\HandlerStack
   */
  public static function getHandlerStack(): \GuzzleHttp\HandlerStack {
    return HandlerStack::create();
  }

  public static function getCHClient(array $config, LoggerInterface $logger, Settings $settings, HmacAuthMiddleware $middleware, EventDispatcherInterface $dispatcher, string $api_version = 'v2'): ContentHubClient {
    return new ContentHubClient($config, $logger, $settings, $middleware, $dispatcher, $api_version);
  }

  public static function getCDFTypeEvent(array $data): GetCDFTypeEvent {
    return new GetCDFTypeEvent($data);
  }

  public static function getCDFDocument(CDFObject ...$entities): CDFDocument {
    return new CDFDocument($entities);
  }

  public static function getWebhook(array $definition): Webhook {
    return new Webhook($definition);
  }
}