<?php

namespace Acquia\ContentHubClient\Middleware;

use Acquia\ContentHubClient\hmacv1\Digest as Digest;
use Acquia\ContentHubClient\hmacv1\Guzzle\HmacAuthMiddleware;
use Acquia\ContentHubClient\hmacv1\RequestSigner;
use Acquia\ContentHubClient\hmacv1\ResponseSigner;
use Symfony\Component\HttpFoundation\Request;

class MiddlewareHmacV1  extends MiddlewareHmacBase implements MiddlewareHmacInterface {

  /**
   * {@inheritdoc}
   */
  public function getMiddleware() {
    $requestSigner = new RequestSigner(new Digest\Version1('sha256'));
    return new HmacAuthMiddleware($requestSigner, $this->apiKey, $this->secretKey);
  }

  /**
   * @return ResponseSigner
   */
  public function getResponseSigner($request) {
    $response = new ResponseSigner($this->api_key, $this->secret);
    $response->signResponse();
    return $response;
  }

  /**
   * Extracts HMAC signature from the request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The Request to evaluate signature.
   * @param string $secret_key
   *   The Secret Key.
   *
   * @return string
   *   A base64 encoded string signature.
   */
  public function authenticate($request) {
    // Extract signature information from the request.
    $headers = array_map('current', $request->headers->all());
    $authorization_header = isset($headers['authorization']) ? $headers['authorization'] : '';

    $http_verb = $request->getMethod();

    // Adding the Request Query string.
    $path = $request->getRequestUri();
    $body = $request->getContent();

    // If the headers are not given, then the request is probably not coming
    // from the Content Hub. Replace them for empty string to fail validation.
    $content_type = isset($headers['content-type']) ? $headers['content-type'] : '';
    $date = isset($headers['date']) ? $headers['date'] : '';
    $message_array = [
      $http_verb,
      md5($body),
      $content_type,
      $date,
      '',
      $path,
    ];
    $message = implode("\n", $message_array);
    $s = hash_hmac('sha256', $message, $this->secretKey, TRUE);
    $signature = base64_encode($s);

    $authorization = 'Acquia ContentHub:' . $signature;

    if ($authorization !== $authorization_header) {
      $this->loggerFactory->get('acquia_contenthub')->debug('HMAC validation failed. [authorization = %authorization]. [authorization_header = %authorization_header]', [
        '%authorization' => $authorization,
        '%authorization_header' => $authorization_header,
      ]);
    }

    return $authorization == $authorization_header;
  }
}
