<?php
/**
 * Created by PhpStorm.
 * User: japerry
 * Date: 3/23/18
 * Time: 10:26 PM
 */

namespace Acquia\ContentHubClient\Middleware;



abstract class MiddlewareHmacBase {

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

  public function __construct($api_key, $secret_key, $version) {
    $this->apiKey = $api_key;
    $this->secretKey = $secret_key;
    $this->version = $version;
  }

  public function setApiKey($api) {
    $this->apiKey = $api;
  }

  public function setSecretKey($secret) {
    $this->secretKey = $secret;
  }

}