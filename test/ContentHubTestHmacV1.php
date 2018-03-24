<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\ContentHub;
use Acquia\ContentHubClient\Middleware\MiddlewareHmacV1;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

class ContentHubTestHmacV1 extends ContentHubTestBase {

  protected function getClient(array $responses = []) {
    $mock = new MockHandler($responses);
    $stack = HandlerStack::create($mock);
    $middleware = new MiddlewareHmacV1('public', 'secret', 'V1');

    return new ContentHub('origin', $middleware, ['handler' => $stack]);
  }

}
