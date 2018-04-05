<?php

namespace Acquia\ContentHubClient\Middleware;

interface MiddlewareHmacInterface {

  /**
   * @return mixed HmacMiddleWare based on which version of HMAC you're using.
   */
  public function getMiddleware();

  /**
   * @return mixed response based on the library's response signer.
   */
  public function getResponseSigner($request);

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

  /**
   * Extracts HMAC signature from the request.
   *
   * @param Request $request
   *   The Request to evaluate signature.
   *
   * @return string
   *   The HMAC signature for this request.
   */
  public function authenticate($request);
}
