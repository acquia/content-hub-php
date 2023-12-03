<?php

namespace Acquia\ContentHubClient;

use Acquia\ContentHubClient\Guzzle\Middleware\RequestResponseHandler;
use Acquia\ContentHubClient\Logging\LoggingHelperTrait;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Common trait for CH Client and CH Logging Client.
 */
trait ContentHubClientTrait {

  use ContentHubClientCommonTrait;
  use LoggingHelperTrait;

  /**
   * GuzzleHttp client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  public $httpClient;

  /**
   * Custom configurations.
   *
   * @var array
   */
  protected $config;

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
  public function ping(): ResponseInterface {
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
    try {
      return $this->httpClient->send($request, $options);
    }
    catch (\Exception $e) {
      return $this->getExceptionResponse($request->getMethod(), $request->getUri()->getPath(), $e);
    }
  }

  /**
   * Create and send an HTTP GET request.
   *
   * @param string $uri
   *   URI object or string.
   * @param array $options
   *   Request options to apply.
   */
  public function get(string $uri, array $options = []): ResponseInterface {
    return $this->request('GET', $uri, $options);
  }

  /**
   * Create and send an HTTP PUT request.
   *
   * @param string $uri
   *   URI object or string.
   * @param array $options
   *   Request options to apply.
   */
  public function put(string $uri, array $options = []): ResponseInterface {
    return $this->request('PUT', $uri, $options);
  }

  /**
   * Create and send an HTTP POST request.
   *
   * @param string $uri
   *   URI object or string.
   * @param array $options
   *   Request options to apply.
   */
  public function post(string $uri, array $options = []): ResponseInterface {
    return $this->request('POST', $uri, $options);
  }

  /**
   * Create and send an HTTP DELETE request.
   *
   * @param string $uri
   *   URI object or string.
   * @param array $options
   *   Request options to apply.
   */
  public function delete(string $uri, array $options = []): ResponseInterface {
    return $this->request('DELETE', $uri, $options);
  }

  /**
   * Sets configurations.
   *
   * @param array $config
   *   Array of configurations.
   */
  public function setConfigs(array $config): void {
    $this->config = $config;
  }

}
