<?php

namespace Acquia\ContentHubClient\Middleware;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;

abstract class MiddlewareHmacBase {

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The api key.
   *
   * @var string
   */
  protected $apiKey;

  /**
   * The secret key.
   *
   * @var string
   */
  protected $secretKey;

  /**
   * The hmac version.
   *
   * @var string
   */
  protected $version;

  /**
   * MiddlewareHmacBase constructor.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   * @param $api_key
   * @param $secret_key
   * @param $version
   */
  public function __construct(LoggerChannelFactoryInterface $logger_factory, $api_key, $secret_key, $version) {
    $this->loggerFactory = $logger_factory;
    $this->apiKey = $api_key;
    $this->secretKey = $secret_key;
    $this->version = $version;
  }

  /**
   * {@inheritdoc}
   */
  public function setApiKey($api) {
    $this->apiKey = $api;
  }

  /**
   * {@inheritdoc}
   */
  public function setSecretKey($secret) {
    $this->secretKey = $secret;
  }

}
