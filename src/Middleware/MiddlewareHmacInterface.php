<?php

namespace Acquia\ContentHubClient\Middleware;

interface MiddlewareHmacInterface {

  /**
   * @return mixed HmacMiddleWare based on which version of HMAC you're using.
   */
  public function getMiddleware();

  /**
   *
   * @return string Api Key
   */
  public function setApiKey($api);

  /**
   *
   * @return string Secret Key
   */
  public function setSecretKey($secret);

}
