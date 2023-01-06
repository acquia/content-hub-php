<?php

namespace Acquia\ContentHubClient;

use Acquia\ContentHubClient\Guzzle\Middleware\RequestResponseHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LogLevel;

/**
 * Common trait for CH Client and CH Logging Client.
 *
 * @method ResponseInterface get(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface put(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface post(string|UriInterface $uri, array $options = [])
 * @method ResponseInterface delete(string|UriInterface $uri, array $options = [])
 */
trait ContentHubClientTrait {

  /**
   * GuzzleHttp client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Attaches RequestResponseHandler to handlers stack.
   *
   * @param array $config
   *   Client config.
   *
   * @codeCoverageIgnore
   */
  protected function addRequestResponseHandler(array $config): void {
    if (empty($config['handler']) || empty($this->logger)) {
      return;
    }

    if (!$config['handler'] instanceof HandlerStack) {
      return;
    }

    $config['handler']->push(new RequestResponseHandler($this->logger));
  }

  /**
   * Removes all leading and trailing slashes.
   *
   * Strip all leading and trailing slashes from all components of the given
   * array.
   *
   * @param string[] $components
   *   Array of strings.
   *
   * @return string[]
   *   Processed array.
   */
  protected static function removeAllLeadingAndTrailingSlashes(array $components): array {
    return array_map(function ($component) {
      return trim($component, '/');
    }, $components);
  }

  /**
   * Glue all elements of an array together.
   *
   * @param array $parts
   *   Parts array.
   * @param string $glue
   *   Glue symbol.
   *
   * @return string
   *   Processed string.
   */
  protected static function gluePartsTogether(array $parts, string $glue): string {
    return implode($glue, self::removeAllLeadingAndTrailingSlashes($parts));
  }

  /**
   * Make a base url out of components and add a trailing slash to it.
   *
   * @param string[] $base_url_components
   *   Base URL components.
   *
   * @return string
   *   Processed string.
   */
  protected static function makeBaseURL(...$base_url_components): string { // phpcs:ignore
    return self::makePath(...$base_url_components) . '/';
  }

  /**
   * Make path out of its individual components.
   *
   * @param string[] $path_components
   *   Path components.
   *
   * @return string
   *   Processed string.
   */
  protected static function makePath(...$path_components): string { // phpcs:ignore
    return self::gluePartsTogether($path_components, '/');
  }

  /**
   * Returns error response.
   *
   * @param int $code
   *   Status code.
   * @param string $reason
   *   Reason.
   * @param string|null $request_id
   *   The request id from the ContentHub service if available.
   *
   * @return \GuzzleHttp\Psr7\Response
   *   Response.
   */
  protected function getErrorResponse($code, $reason, $request_id = NULL) {
    if ($code < 100 || $code >= 600) {
      $code = 500;
    }
    $body = [
      'request_id' => $request_id,
      'error' => [
        'code' => $code,
        'message' => $reason,
      ],
    ];
    return new Response($code, [], json_encode($body), '1.1', $reason);
  }

  /**
   * Obtains the appropriate exception Response, logging error messages according to API call.
   *
   * @param string $method
   *   The Request to Plexus, as defined in the content-hub-php library.
   * @param array $args
   *   The Request arguments.
   * @param \Exception $exception
   *   The Exception object.
   *
   * @return \GuzzleHttp\Psr7\ResponseInterface
   *   The response after raising an exception.
   *
   *  @codeCoverageIgnore
   */
  protected function getExceptionResponse($method, array $args, \Exception $exception) {
    // If we reach here it is because there was an exception raised in the API call.
    $api_call = $args[0];
    $response = $exception->getResponse();
    if (!$response) {
      $response = $this->getErrorResponse($exception->getCode(), $exception->getMessage());
    }
    $response_body = json_decode($response->getBody(), TRUE);
    $error_code = $response_body['error']['code'] ?? '';
    $error_message = $response_body['error']['message'] ?? '';

    // Customize Error messages according to API Call.
    switch ($api_call) {
      case'settings/webhooks':
        $log_level = LogLevel::WARNING;
        break;

      case (preg_match('/filters\?name=*/', $api_call) ? TRUE : FALSE):
      case (preg_match('/settings\/clients\/*/', $api_call) ? TRUE : FALSE):
      case (preg_match('/settings\/webhooks\/.*\/filters/', $api_call) ? TRUE : FALSE):
        $log_level = LogLevel::NOTICE;
        break;

      default:
        // The default log level is ERROR.
        $log_level = LogLevel::ERROR;
        break;
    }

    $reason = sprintf("Request ID: %s, Method: %s, Path: \"%s\", Status Code: %s, Reason: %s, Error Code: %s, Error Message: \"%s\". Error data: \"%s\"",
      $response_body['request_id'] ?? '',
      strtoupper($method),
      $api_call,
      $response->getStatusCode(),
      $response->getReasonPhrase(),
      $error_code,
      $error_message,
      print_r($response_body['error']['data'] ?? $response_body['error'] ?? '', TRUE)
    );
    $this->logger->log($log_level, $reason);

    // Return the response.
    return $response;
  }

  /**
   * Get the settings that were used to instantiate this client.
   *
   * @return \Acquia\ContentHubClient\Settings
   *   Settings object.
   *
   * @codeCoverageIgnore
   */
  public function getSettings() {
    return $this->settings;
  }

  /**
   * Gets a Json Response from a request.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   Response.
   *
   * @return mixed
   *   Response array.
   *
   * @throws \Exception
   */
  public static function getResponseJson(ResponseInterface $response) {
    try {
      $body = (string) $response->getBody();
    }
    catch (\Exception $exception) {
      $message = sprintf("An exception occurred in the JSON response. Message: %s",
        $exception->getMessage());
      throw new \Exception($message);
    }

    return json_decode($body, TRUE);
  }

  /**
   * Pings the service to ensure that it is available.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   * @throws \Exception
   *
   * @since 0.2.0
   */
  public function ping() {
    $makeBaseURL = self::makeBaseURL($this->getConfig()['base_url']);
    $client = ObjectFactory::getGuzzleClient([
      'base_uri' => $makeBaseURL,
    ]);

    return $client->get('ping');
  }

  /**
   * {@inheritdoc}
   */
  public function send(RequestInterface $request, array $options = []): ResponseInterface {
    return $this->httpClient->send($request, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface {
    return $this->httpClient->sendAsync($request, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function request($method, $uri, array $options = []): ResponseInterface {
    return $this->httpClient->request($method, $uri, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function requestAsync($method, $uri, array $options = []): PromiseInterface {
    return $this->httpClient->requestAsync($method, $uri, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig($option = NULL) {
    return $this->httpClient->getConfig($option);
  }

}
