<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\ContentHub;
use Acquia\ContentHubClient\Middleware\MiddlewareHmacV1;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

class SettingsTestHmacV1 extends SettingsTestBase {

  /**
   * @param array $responses Responses
   *
   * @return \Acquia\ContentHubClient\ContentHub
   */
   protected function getClient(array $responses = []) {
     $mock = new MockHandler($responses);
     $stack = HandlerStack::create($mock);
     $middleware = new MiddlewareHmacV1('public', 'secret', 'V1');

     return new ContentHub('origin', $middleware, ['handler' => $stack]);
   }
}
