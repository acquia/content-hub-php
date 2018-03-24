<?php

namespace Acquia\ContentHubClient\Middleware;

interface MiddlewareHmacInterface {

  public function getMiddleware();

  public function setApiKey($api);

  public function setSecretKey($secret);

}
