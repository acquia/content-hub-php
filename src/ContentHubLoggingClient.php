<?php

namespace Acquia\ContentHubClient;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use Acquia\ContentHubClient\Guzzle\Middleware\RequestResponseHandler;
use Acquia\ContentHubClient\SearchCriteria\SearchCriteria;
use Acquia\ContentHubClient\SearchCriteria\SearchCriteriaBuilder;
use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

use function GuzzleHttp\default_user_agent;

/**
 * Class ContentHubClient.
 *
 * @package Acquia\ContentHubClient
 */
class ContentHubLoggingClient extends Client {

  // Override VERSION inherited from GuzzleHttp::ClientInterface.
  const VERSION = '2.0.0';

  const LIBRARYNAME = 'AcquiaContentHubPHPLib';

  const OPTION_NAME_LANGUAGES = 'client-languages';

  const FEATURE_DEPRECATED_RESPONSE = [
    'success' => FALSE,
    'error' => [
      'code' => SymfonyResponse::HTTP_GONE,
      'message' => 'This feature is deprecated',
    ],
  ];

  /**
   * The settings.
   *
   * @var \Acquia\ContentHubClient\Settings
   */
  protected $settings;

  /**
   * The logger responsible for tracking request failures.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  // phpcs:disable
  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $config = [],
    LoggerInterface $logger,
    Settings $settings,
    HmacAuthMiddleware $middleware,
    EventDispatcherInterface $dispatcher
  ) {
    $this->logger = $logger;
    $this->settings = $settings;
    $this->dispatcher = $dispatcher;

    // "base_url" parameter changed to "base_uri" in Guzzle6, so the following line
    // is there to make sure it does not disrupt previous configuration.
    if (!isset($config['base_uri']) && isset($config['base_url'])) {
      $config['base_uri'] = self::makeBaseURL($config['base_url']);
    }
    else {
      $config['base_uri'] = self::makeBaseURL($config['base_uri']);
    }

    // Setting up the User Header string.
    $user_agent_string = self::LIBRARYNAME . '/' . self::VERSION . ' ' . default_user_agent();
    if (isset($config['client-user-agent'])) {
      $user_agent_string = $config['client-user-agent'] . ' ' . $user_agent_string;
    }

    // Setting up the headers.
    $config['headers']['Content-Type'] = 'application/json';
    $config['headers']['X-Acquia-Plexus-Client-Id'] = $settings->getUuid();
    $config['headers']['User-Agent'] = $user_agent_string;

    // Add the authentication handler.
    // @see https://github.com/acquia/http-hmac-spec
    if (!isset($config['handler'])) {
      $config['handler'] = ObjectFactory::getHandlerStack();
    }
    $config['handler']->push($middleware);
    $this->addRequestResponseHandler($config);

    parent::__construct($config);
  }
  // phpcs:enable

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

    return self::getResponseJson($client->get('ping'));
  }


  /**
   * Sends event log to events micro service.
   *
   * @param string $status
   *   Status for the event: error, warning etc.
   * @param string $message
   *   Error message to display.
   * @param array $context
   *   Context array containing uuid, relevant score etc.
   *
   * @return mixed
   *   Response from logging service.
   *
   * @throws Exception
   */
  public function sendLog(string $status, string $message, array $context) {
    $log_details = $this->getContextArray($status, $message, $context);
    $log_details['origin'] = $this->settings->getUuid();
    $options['body'] = json_encode($log_details);
    return self::getResponseJson($this->post('events', $options));
  }

  /**
   * Sets the array for event logging.
   *
   * @param string $status
   *   Status for the event: error, warning etc.
   * @param string $message
   *   Error message to display.
   * @param array $context
   *   Context array containing uuid, relevant score etc.
   *
   * @return array
   *   Final context array having all the required attributes.
   *
   * @throws Exception
   */
  public function getContextArray(string $status, string $message, array $context): array {
    if (isset(
      $context['object_id'],
      $context['event_name'],
      $context['object_type'],
      $context['relevant_score']
    )) {
      $context['status'] = $status;
      $context['content'] = json_encode(['message' => $message]);
    }
    else {
      throw new \Exception('Object Id(UUID) / Event Name/ Object Type/ Relevant score missing from event log attributes');
    }
    return $context;
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
    catch (Exception $exception) {
      $message = sprintf("An exception occurred in the JSON response. Message: %s",
        $exception->getMessage());
      throw new Exception($message);
    }

    return json_decode($body, TRUE);
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function __call($method, $args) {
    try {
      if (strpos($args[0], '?')) {
        [$uri, $query] = explode('?', $args[0]);
        $parts = explode('/', $uri);
        if ($query) {
          $last = array_pop($parts);
          $last .= "?$query";
          $parts[] = $last;
        }
      }
      else {
        $parts = explode('/', $args[0]);
      }
      $args[0] = self::makePath(...$parts);

      return parent::__call($method, $args);
    }
    catch (Exception $e) {
      return $this->getExceptionResponse($method, $args, $e);
    }
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
   * @return ResponseInterface The response after raising an exception.
   *   The response object.
   *
   *  @codeCoverageIgnore
   */
  protected function getExceptionResponse($method, array $args, \Exception $exception)
  {
    // If we reach here it is because there was an exception raised in the API call.
    $api_call = $args[0];
    $response = $exception->getResponse();
    if (!$response) {
      $response = $this->getErrorResponse($exception->getCode(), $exception->getMessage());
    }
    $response_body = json_decode($response->getBody(), TRUE);
    $error_code = $response_body['error']['code'];
    $error_message = $response_body['error']['message'];

    // Customize Error messages according to API Call.
    switch ($api_call) {
      case'settings/webhooks':
        $log_level = LogLevel::WARNING;
        break;

      case (preg_match('/filters\?name=*/', $api_call) ? true : false) :
      case (preg_match('/settings\/clients\/*/', $api_call) ? true : false) :
      case (preg_match('/settings\/webhooks\/.*\/filters/', $api_call) ? true : false) :
        $log_level = LogLevel::NOTICE;
        break;

      default:
        // The default log level is ERROR.
        $log_level = LogLevel::ERROR;
        break;
    }

    $reason = sprintf("Request ID: %s, Method: %s, Path: \"%s\", Status Code: %s, Reason: %s, Error Code: %s, Error Message: \"%s\"",
      $response_body['request_id'],
      strtoupper($method),
      $api_call,
      $response->getStatusCode(),
      $response->getReasonPhrase(),
      $error_code,
      $error_message
    );
    $this->logger->log($log_level, $reason);

    // Return the response.
    return $response;
  }

  /**
   * Returns error response.
   *
   * @param int $code
   *   Status code.
   * @param string $reason
   *   Reason.
   * @param null $request_id
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
      ]
    ];
    return new Response($code, [], json_encode($body), '1.1', $reason);
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

}
