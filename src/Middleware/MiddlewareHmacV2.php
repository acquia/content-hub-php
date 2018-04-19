<?php

namespace Acquia\ContentHubClient\Middleware;

use Acquia\Hmac\AuthorizationHeader;
use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\KeyLoader;
use Acquia\Hmac\ResponseSigner;
use GuzzleHttp\Psr7\Response;
use Acquia\Hmac\RequestAuthenticator;
use Acquia\Hmac\Exception\KeyNotFoundException;

class MiddlewareHmacV2  extends MiddlewareHmacBase implements MiddlewareHmacInterface {

  /**
   * The hmacv2 keyloader object.
   *
   * @var \Acquia\Hmac\KeyLoader
   */
  protected $keyLoader;

  /**
   * MiddlewareHmacV2 constructor.
   * @param $api_key
   * @param $secret_key
   * @param $version
   */
  public function __construct($api_key, $secret_key, $version) {
    parent::__construct($api_key, $secret_key, $version);
    $this->keyLoader = new KeyLoader([$api_key => $secret_key]);
  }

  /**
   * {@inheritdoc}
   */
  public function getMiddleware() {
    return new HmacAuthMiddleware($this->keyLoader->load($this->apiKey));
  }

  /**
   * {@inheritdoc}
   */
  public function getResponseSigner($request) {
    $signer = new ResponseSigner($this->keyLoader->load($this->apiKey), $request);
    $response = new Response();
    return $signer->signResponse($response);
  }

  /**
   * @param \GuzzleHttp\Psr7\Request $request
   * @return bool|string
   */
  public function authenticate($request) {

    $authenticator = new RequestAuthenticator($this->keyLoader);

    // $request implements PSR-7's \Psr\Http\Message\RequestInterface
    // An exception will be thrown if it cannot authenticate.
    try {
      $key = $authenticator->authenticate($request);
    }
    catch (KeyNotFoundException $exception) {
      // @TODO: We should make this better someday...
      $this->loggerFactory->get('acquia_contenthub')->debug('HMAC validation failed. [authorization = %authorization]. [authorization_header = %authorization_header]', [
        '%authorization' => '',
        '%authorization_header' => $request->headers->get('authorization'),
      ]);
      return FALSE;
    }
    return TRUE;
  }

  public function getSignature($request, $secret) {
    $authHeader = AuthorizationHeader::createFromRequest($request);
    return $authHeader->getSignature();
  }
}
