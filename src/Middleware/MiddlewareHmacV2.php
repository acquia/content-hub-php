<?php

namespace Acquia\ContentHubClient\Middleware;

use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;

class MiddlewareHmacV2  extends MiddlewareHmacBase implements MiddlewareHmacInterface {

  /**
   * {@inheritdoc}
   */
  public function getMiddleware() {
    $key = new Key($this->apiKey, $this->secretKey);
    return new HmacAuthMiddleware($key);
  }

}
