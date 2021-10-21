<?php

namespace Acquia\ContentHubClient;

use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Exception;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use function GuzzleHttp\default_user_agent;

/**
 * Class ContentHubClient.
 *
 * @package Acquia\ContentHubClient
 */
class ContentHubLoggingClient extends Client {

  use ContentHubClientTrait;

  // Override VERSION inherited from GuzzleHttp::ClientInterface.
  const VERSION = '2.0.0';

  const LIBRARYNAME = 'AcquiaContentHubPHPLib';

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
    LoggerInterface $logger,
    Settings $settings,
    HmacAuthMiddleware $middleware,
    EventDispatcherInterface $dispatcher,
    array $config = []
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
   * Sends event log to events micro service.
   *
   * @param string $severity
   *   Severity for the event: ERROR, INFO, WARN etc.
   * @param string $message
   *   Error message to display.
   * @param array $context
   *   Context array containing uuid, object type etc.
   *
   * @return mixed
   *   Response from logging service.
   *
   * @throws Exception
   */
  public function sendLog(string $severity, string $message, array $context) {
    $log_details = $this->getContextArray($severity, $message, $context);
    $log_details['origin'] = $this->getSettings()->getUuid();
    $options['body'] = json_encode($log_details);

    return self::getResponseJson($this->post('events', $options));
  }

  /**
   * Sets the array for event logging.
   *
   * @param string $severity
   *   Severity for the event: ERROR, INFO, WARN etc.
   * @param string $message
   *   Error message to display.
   * @param array $context
   *   Context array containing uuid, object type etc.
   *
   * @return array
   *   Final context array having all the required attributes.
   *
   * @throws Exception
   */
  public function getContextArray(string $severity, string $message, array $context): array {
    if (isset(
      $context['object_id'],
      $context['event_name'],
      $context['object_type']
    )) {
      $context['severity'] = $severity;
      $context['content'] = $message;
    }
    else {
      throw new \Exception('Object Id(UUID) / Event Name/ Object Type missing from event log attributes');
    }
    return $context;
  }

}
