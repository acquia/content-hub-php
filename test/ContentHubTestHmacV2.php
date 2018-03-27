<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\ContentHub;
use Acquia\ContentHubClient\Middleware\MiddlewareHmacV2;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

class ContentHubTestHmacV2 extends ContentHubTestBase {

  protected function getClient(array $responses = []) {
    $mock = new MockHandler($responses);
    $stack = HandlerStack::create($mock);
    $middleware = new MiddlewareHmacV2('public', 'secret', 'V2');

    return new ContentHub('origin', $middleware, ['handler' => $stack]);
  }

}
