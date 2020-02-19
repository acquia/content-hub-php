<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\ContentHub;
use Acquia\ContentHubClient\Middleware\MiddlewareHmacV1;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

/**
 * Class ContentHubTestHmacV1.
 *
 * @package Acquia\ContentHubClient\test
 */
class ContentHubTestHmacV1 extends ContentHubTestBase {

  /**
   * {@inheritDoc}
   */
  protected function getClient(array $responses = []) {
    $mock = new MockHandler($responses);
    $stack = HandlerStack::create($mock);
    $middleware = new MiddlewareHmacV1('public', 'secret', 'V1');

    return new ContentHub('origin', $middleware, ['handler' => $stack]);
  }

}
