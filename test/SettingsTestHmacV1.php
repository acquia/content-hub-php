<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\ContentHub;
use Acquia\ContentHubClient\Middleware\MiddlewareHmacV1;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

/**
 * Class SettingsTestHmacV1.
 *
 * @package Acquia\ContentHubClient\test
 */
class SettingsTestHmacV1 extends SettingsTestBase {

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
