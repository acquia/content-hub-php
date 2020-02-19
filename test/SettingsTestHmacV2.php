<?php

namespace Acquia\ContentHubClient\test;

use Acquia\ContentHubClient\ContentHub;
use Acquia\ContentHubClient\Middleware\MiddlewareHmacV2;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

/**
 * Class SettingsTestHmacV2.
 *
 * @package Acquia\ContentHubClient\test
 */
class SettingsTestHmacV2 extends SettingsTestBase {

  /**
   * {@inheritDoc}
   */
  protected function getClient(array $responses = []) {
    $mock = new MockHandler($responses);
    $stack = HandlerStack::create($mock);
    $middleware = new MiddlewareHmacV2('public', 'secret', 'V2');

    return new ContentHub('origin', $middleware, ['handler' => $stack]);
  }

}
