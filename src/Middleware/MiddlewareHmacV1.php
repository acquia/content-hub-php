<?php

namespace Acquia\ContentHubClient\Middleware;

use Acquia\ContentHubClient\hmacv1\Digest as Digest;
use Acquia\ContentHubClient\hmacv1\Guzzle\HmacAuthMiddleware;
use Acquia\ContentHubClient\hmacv1\RequestSigner;

class MiddlewareHmacV1  extends MiddlewareHmacBase implements MiddlewareHmacInterface {

  /**
   * {@inheritdoc}
   */
  public function getMiddleware() {
    $requestSigner = new RequestSigner(new Digest\Version1('sha256'));
    return new HmacAuthMiddleware($requestSigner, $this->apiKey, $this->secretKey);
  }
}
