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
class ContentHubClient extends Client {

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
    EventDispatcherInterface $dispatcher,
    $api_version = 'v2'
  ) {
    $this->logger = $logger;
    $this->settings = $settings;
    $this->dispatcher = $dispatcher;

    // "base_url" parameter changed to "base_uri" in Guzzle6, so the following line
    // is there to make sure it does not disrupt previous configuration.
    if (!isset($config['base_uri']) && isset($config['base_url'])) {
      $config['base_uri'] = self::makeBaseURL($config['base_url'],
        $api_version);
    }
    else {
      $config['base_uri'] = self::makeBaseURL($config['base_uri'],
        $api_version);
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
   * Discoverability of the API.
   *
   * @param string $endpoint
   *   Endpoint URI.
   *
   * @return array
   *   Response.
   *
   * @throws \Exception
   * @codeCoverageIgnore
   */
  public function definition($endpoint = '') {
    return self::getResponseJson($this->request('options', $endpoint));
  }

  /**
   * Registers a new client for the active subscription.
   *
   * This method also returns the UUID for the new client being registered.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   Logger.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event dispatcher.
   * @param string $name
   *   The human-readable name for the client.
   * @param string $url
   *   URL.
   * @param string $api_key
   *   API key.
   * @param string $secret
   *   API secret.
   * @param string $api_version
   *   API version.
   *
   * @return \Acquia\ContentHubClient\ContentHubClient
   *   ContentHubClient instance.
   *
   * @throws \Exception
   */
  public static function register(
    LoggerInterface $logger,
    EventDispatcherInterface $dispatcher,
    $name,
    $url,
    $api_key,
    $secret,
    $api_version = 'v2'
  ) {
    $config = [
      'base_uri' => self::makeBaseURL($url, $api_version),
      'headers' => [
        'Content-Type' => 'application/json',
        'User-Agent' => self::LIBRARYNAME . '/' . self::VERSION . ' ' . default_user_agent(),
      ],
      'handler' => ObjectFactory::getHandlerStack(),
    ];

    // Add the authentication handler.
    // @see https://github.com/acquia/http-hmac-spec
    $key = ObjectFactory::getAuthenticationKey($api_key, $secret);
    $middleware = ObjectFactory::getHmacAuthMiddleware($key);
    $config['handler']->push($middleware);
    $client = ObjectFactory::getGuzzleClient($config);
    $options['body'] = json_encode(['name' => $name]);
    try {
      $response = $client->post('register', $options);
      $values = self::getResponseJson($response);
      $settings = ObjectFactory::instantiateSettings($values['name'],
        $values['uuid'], $api_key, $secret, $url);
      $config = [
        'base_url' => $settings->getUrl(),
      ];
      $client = ObjectFactory::getCHClient($config, $logger, $settings,
        $settings->getMiddleware(), $dispatcher);
      // @todo remove this once shared secret is returned on the register
      // endpoint.
      // We need the shared secret to be fully functional, so an additional
      // request is required to get that.
      $remote = $client->getRemoteSettings();
      // Now that we have the shared secret, reinstantiate everything and
      // return a new instance of this class.
      $settings = ObjectFactory::instantiateSettings($settings->getName(),
        $settings->getUuid(), $settings->getApiKey(), $settings->getSecretKey(),
        $settings->getUrl(), $remote['shared_secret']);
      return ObjectFactory::getCHClient($config, $logger, $settings,
        $settings->getMiddleware(), $dispatcher);
    }
    catch (Exception $exception) {
      if ($exception instanceof BadResponseException) {
        $message = sprintf('Error registering client with name="%s" (Error Code = %d: %s)',
          $name, $exception->getResponse()->getStatusCode(),
          $exception->getResponse()->getReasonPhrase());
        $logger->error($message);
        throw new RequestException($message, $exception->getRequest(),
          $exception->getResponse());
      }
      if ($exception instanceof RequestException) {
        $message = sprintf('Could not get authorization from Content Hub to register client %s. Are your credentials inserted correctly? (Error message = %s)',
          $name, $exception->getMessage());
        $logger->error($message);
        throw new RequestException($message, $exception->getRequest(),
          $exception->getResponse());
      }
      $message = sprintf("An unknown exception was caught. Message: %s",
        $exception->getMessage());
      $logger->error($message);
      throw new Exception($message);
    }
  }

  /**
   * Checks Plexus to see if the client name is already in use.
   *
   * @param string $name
   *   Name.
   * @param string $url
   *   URL.
   * @param string $api_key
   *   API key.
   * @param string $secret
   *   API secret.
   * @param string $api_version
   *   API version.
   *
   * @return bool
   *   Whether the clientName from the request matches the name passed to it.
   */
  public static function clientNameExists(
    $name,
    $url,
    $api_key,
    $secret,
    $api_version = 'v2'
  ) {
    $config = [
      'base_uri' => self::makeBaseURL($url, $api_version),
      'headers' => [
        'Content-Type' => 'application/json',
        'User-Agent' => self::LIBRARYNAME . '/' . self::VERSION . ' ' . default_user_agent(),
      ],
      'handler' => ObjectFactory::getHandlerStack(),
    ];

    // Add the authentication handler.
    // @see https://github.com/acquia/http-hmac-spec
    $key = ObjectFactory::getAuthenticationKey($api_key, $secret);
    $middleware = ObjectFactory::getHmacAuthMiddleware($key);
    $config['handler']->push($middleware);
    $client = ObjectFactory::getGuzzleClient($config);
    $options['body'] = json_encode(['name' => $name]);
    // Attempt to fetch the client name, if it works.
    try {
      $client->get("settings/clients/$name");

      return TRUE;
    }
    catch (ClientException $error) {
      return $error->getResponse()->getStatusCode() !== HttpResponse::HTTP_NOT_FOUND;
    }
  }

  /**
   * Sends request to asynchronously create entities.
   *
   * phpcs:ignore @param \Acquia\ContentHubClient\CDF\CDFObject ...$objects
   *   Individual CDFObjects to send to ContentHub.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   */
  public function createEntities(CDFObject ...$objects) {
    $json = [
      'resource' => "",
    ];
    foreach ($objects as $object) {
      $json['entities'][] = $object->toArray();
    }
    $options['body'] = json_encode($json);
    return $this->post('entities', $options);
  }

  /**
   * Returns an entity by UUID.
   *
   * @param string $uuid
   *   UUID.
   *
   * @return \Acquia\ContentHubClient\CDF\CDFObjectInterface|array
   *   A CDFObject representing the entity or an array if there was no data.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   * @throws \Exception
   *
   * @todo can we return a CDFObject here?
   */
  public function getEntity($uuid) {
    $return = self::getResponseJson($this->get("entities/$uuid"));
    if (!empty($return['data']['data'])) {
      return $this->getCDFObject($return['data']['data']);
    }

    return $return;
  }

  /**
   * Searches for entities.
   *
   * @param array $uuids
   *   An array of UUIDs.
   *
   * @return \Acquia\ContentHubClient\CDFDocument
   *   CDFDocument instance.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   * @throws \Exception
   */
  public function getEntities(array $uuids) {
    $chunks = array_chunk($uuids, 50);
    $objects = [];
    foreach ($chunks as $chunk) {
      $query = [
        'size' => 50,
        'query' => [
          'constant_score' => [
            'filter' => [
              'terms' => [
                'uuid' => $chunk,
              ],
            ],
          ],
        ],
      ];
      $options['body'] = json_encode($query);
      $results = self::getResponseJson($this->get('_search', $options));
      if (isset($results['hits']['total'])) {
        foreach ($results['hits']['hits'] as $key => $item) {
          $objects[] = $this->getCDFObject($item['_source']['data']);
        }
      }
    }

    return ObjectFactory::getCDFDocument(...$objects);
  }

  /**
   * Retrieves a CDF Object.
   *
   * @param mixed $data
   *   Data.
   *
   * @return \Acquia\ContentHubClient\CDF\CDFObjectInterface
   *   CDFObject
   */
  protected function getCDFObject($data) { // phpcs:ignore
    $event = ObjectFactory::getCDFTypeEvent($data);
    $this->dispatcher->dispatch(ContentHubLibraryEvents::GET_CDF_CLASS, $event);

    return $event->getObject();
  }

  /**
   * Updates many entities asynchronously.
   *
   * phpcs:ignore @param \Acquia\ContentHubClient\CDF\CDFObject ...$objects
   *   The CDFObjects to update.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   */
  public function putEntities(CDFObject ...$objects) {
    $json = [
      'resource' => '',
    ];

    foreach ($objects as $object) {
      $json['data']['entities'][] = $object->toArray();
    }

    $options['body'] = json_encode($json);

    return $this->put('entities', $options);
  }

  /**
   * Post entities.
   *
   * phpcs:ignore @param \Acquia\ContentHubClient\CDF\CDFObject ...$objects
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   */
  public function postEntities(CDFObject ...$objects) {
    $json = [
      'resource' => "",
    ];
    foreach ($objects as $object) {
      $json['data']['entities'][] = $object->toArray();
    }
    $options['body'] = json_encode($json);
    return $this->post('entities', $options);
  }

  /**
   * Deletes an entity by UUID.
   *
   * @param string $uuid
   *   Entity UUID.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function deleteEntity($uuid) {
    return $this->delete("entities/$uuid");
  }

  /**
   * Deletes an entity from a webhook's interest list.
   *
   * @param string $uuid
   *   Interest UUID.
   * @param string $webhook_uuid
   *   Webhook UUID.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   */
  public function deleteInterest($uuid, $webhook_uuid) {
    return $this->delete("/interest/$uuid/$webhook_uuid");
  }

  /**
   * Purges all entities from the Content Hub.
   *
   * This method should be used carefully as it deletes all the entities for
   * the current subscription from the Content Hub. This creates a backup that
   * can be restored at any time. Any subsequent purges overwrite the existing
   * backup. Be VERY careful when using this endpoint.
   *
   * @return mixed
   *   Response.
   *
   * @throws \Exception
   */
  public function purge() {
    return self::getResponseJson($this->post('entities/purge'));
  }

  /**
   * Restores the state of entities before the previous purge.
   *
   * Only to be used if a purge has been called previously. This means new
   * entities added after the purge was enacted will be overwritten by the
   * previous state. Be VERY careful when using this endpoint.
   *
   * @return mixed
   *   Response.
   *
   * @throws \Exception
   */
  public function restore() {
    return self::getResponseJson($this->post('entities/restore'));
  }

  /**
   * Reindex a subscription.
   *
   * Schedules a reindex process.
   *
   * @return mixed
   *   Response.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   * @throws \Exception
   */
  public function reindex() {
    return self::getResponseJson($this->post('reindex'));
  }

  /**
   * Obtains Customer-Facing-Logs for the subscription.
   *
   * This is forward search request to Elastic Search.
   *
   * @param string $query
   *   An elastic search query.
   * @param array $query_options
   *   An array with the number of items to show in the list and offset.
   *
   * @return mixed
   *   Response.
   *
   * @throws \Exception
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function logs($query = '', array $query_options = []) {
    return new Response(
      self::FEATURE_DEPRECATED_RESPONSE['error']['code'],
      [],
      json_encode(self::FEATURE_DEPRECATED_RESPONSE),
      '1.1',
      self::FEATURE_DEPRECATED_RESPONSE['error']['message']
    );
  }

  /**
   * Retrieves active ElasticSearch mapping of entities.
   *
   * @return mixed
   *   Response.
   *
   * @throws \Exception
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function mapping() {
    return self::getResponseJson($this->get('_mapping'));
  }

  /**
   * Lists Entities from the Content Hub.
   *
   * Example of how to structure the $options parameter:
   * <code>
   * $options = [
   *     'limit'  => 20,
   *     'type'   => 'node',
   *     'origin' => '11111111-1111-1111-1111-111111111111',
   *     'fields' => 'status,title,body,field_tags,description',
   *     'filters' => [
   *         'status' => 1,
   *         'title' => 'New*',
   *         'body' => '/Boston/',
   *     ],
   * ];
   * </code>
   *
   * @param array $options
   *   Query options.
   *
   * @return mixed
   *   Response.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   * @throws \Exception
   */
  public function listEntities(array $options = []) {
    $variables = $options + [
      'limit' => 1000,
      'start' => 0,
      'filters' => [],
    ];

    foreach ($variables['filters'] as $key => $value) {
      $variables["filter:${key}"] = $value;
    }
    unset($variables['filters']);

    // Now make the request.
    return self::getResponseJson($this->get('entities?' . http_build_query($variables)));
  }

  /**
   * Searches for entities.
   *
   * @param mixed $query
   *   Search query.
   *
   * @return mixed
   *   Response.
   *
   * @throws \Exception
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function searchEntity($query) {
    $options['body'] = json_encode((array) $query);
    return self::getResponseJson($this->get('_search', $options));
  }

  /**
   * Returns the Client, given the site name.
   *
   * @param string $name
   *   Client name.
   *
   * @return mixed
   *   Response.
   *
   * @throws \Exception
   */
  public function getClientByName($name) {
    return self::getResponseJson($this->get("settings/clients/$name"));
  }

  /**
   * Returns clients.
   *
   * @return mixed
   *   Clients list.
   *
   * @throws \Exception
   */
  public function getClients() {
    $data = $this->getRemoteSettings();

    return $data['clients'] ?? [];
  }

  /**
   * Returns webhooks list.
   *
   * @return \Acquia\ContentHubClient\Webhook[]
   *   Webhooks list.
   *
   * @throws \Exception
   */
  public function getWebHooks() {
    $data = $this->getRemoteSettings();
    $webhooks = $data['webhooks'] ?? [];
    array_walk($webhooks, function (&$webhook) {
      $webhook = ObjectFactory::getWebhook($webhook);
    });
    return $webhooks;
  }

  /**
   * Returns webhook by URL.
   *
   * @param string $url
   *   URL.
   *
   * @return array
   *   Webhook.
   *
   * @throws \Exception
   * @codeCoverageIgnore
   */
  public function getWebHook($url) {
    return current(array_filter($this->getWebHooks(),
        function (Webhook $webhook) use ($url) {
          return $webhook->getUrl() === $url;
        })) ?? [];
  }

  /**
   * Returns interests list.
   *
   * @param string $webhook_uuid
   *   Webhook UUID.
   *
   * @return array
   *   Interests list.
   *
   * @throws \Exception
   */
  public function getInterestsByWebhook($webhook_uuid) {
    $data = self::getResponseJson($this->get("/interest/webhook/$webhook_uuid"));

    return $data['data']['interests'] ?? [];
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
   * Obtains the Settings for the active subscription.
   *
   * @return Settings
   *   Response.
   *
   * @throws \Exception
   * @codeCoverageIgnore
   */
  public function getRemoteSettings() {
    return self::getResponseJson($this->get('settings'));
  }

  /**
   * Adds a webhook to the active subscription.
   *
   * @param string $webhook_url
   *   Webhook URL.
   *
   * @return mixed
   *   Response.
   *
   * @throws \Exception
   */
  public function addWebhook($webhook_url) {
    $options['body'] = json_encode(['url' => $webhook_url, 'version' => 2.0]);

    return self::getResponseJson($this->post('settings/webhooks', $options));
  }

  /**
   * Deletes a webhook from the active subscription.
   *
   * @param string $uuid
   *   The UUID of the webhook to delete.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function deleteWebhook($uuid) {
    return $this->delete("settings/webhooks/$uuid");
  }

  /**
   * Updates a webhook from the active subscription.
   *
   * @param string $uuid
   *   The UUID of the webhook to update.
   * @param array $options
   *   What to change in the webhook: url, version, disable_retries, etc.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function updateWebhook($uuid, array $options) {
    if (isset($options['version']) && !in_array($options['version'], [1, 2],
        TRUE)) {
      $options['version'] = 2;
    }
    $acceptable_keys = [
      'version',
      'url',
      'disable_retries',
      'status',
    ];
    $values = [];
    foreach ($acceptable_keys as $key) {
      if (isset($options[$key])) {
        $values[$key] = $options[$key];
      }
    }
    $data['body'] = json_encode($values);
    return $this->put("settings/webhooks/$uuid", $data);
  }

  /**
   * Suppress webhook.
   *
   * @param string $webhook_uuid
   *   Webhook uuid.
   *
   * @return mixed
   *   Response body of backend call.
   */
  public function suppressWebhook(string $webhook_uuid) {
    return self::getResponseJson($this->put("webhook/$webhook_uuid/suppress"));
  }

  /**
   * Remove suppression from webhook.
   *
   * @param string $webhook_uuid
   *   Webhook uuid.
   *
   * @return mixed
   *   Response body of backend call.
   */
  public function unSuppressWebhook(string $webhook_uuid) {
    return self::getResponseJson($this->delete("webhook/$webhook_uuid/suppress"));
  }

  /**
   * Add entities to Interest List.
   *
   * @param string $webhook_uuid
   *   The UUID of the webhook.
   * @param array $uuids
   *   Entity UUIDs to add to Interest List.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function addEntitiesToInterestList($webhook_uuid, array $uuids) {
    $options['body'] = json_encode(['interests' => $uuids]);

    return $this->post("interest/webhook/$webhook_uuid", $options);
  }

  /**
   * Deletes a client from the active subscription.
   *
   * @param string $client_uuid
   *   The UUID of the client to delete, blank for current client.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   *
   * @throws \Exception
   */
  public function deleteClient($client_uuid = NULL) {
    $settings = $this->getSettings();
    $uuid = $client_uuid ?? $settings->getUuid();
    $response = $this->deleteEntity($uuid);
    if (!$response) {
      throw new Exception(sprintf("Entity with UUID = %s cannot be deleted.", $uuid));
    }
    return $this->delete("settings/client/uuid/$uuid");
  }

  /**
   * Updates a client from the active subscription.
   *
   * @param string $uuid
   *   The UUID of the client to update.
   * @param string $name
   *   The new name for the client we're updating.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function updateClient($uuid, $name) {
    $options['body'] = json_encode(['name' => $name]);
    return $this->put("settings/client/uuid/$uuid", $options);
  }

  /**
   * Regenerates a Shared Secret for the Subscription.
   *
   * @return array
   *   Response.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   * @throws \Exception
   */
  public function regenerateSharedSecret() {
    return self::getResponseJson($this->post('settings/secret', ['body' => json_encode([])]));
  }

  /**
   * Gets filter by UUID.
   *
   * @param string $filter_id
   *   The filter UUID.
   *
   * @return array
   *   Response
   *
   * @throws \GuzzleHttp\Exception\RequestException
   * @throws \Exception
   */
  public function getFilter($filter_id) {
    return $this::getResponseJson($this->get("filters/$filter_id"));
  }

  /**
   * Gets filter by Name.
   *
   * @param string $filter_name
   *   The filter name.
   *
   * @return array
   *   The filter array.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   * @throws \Exception
   */
  public function getFilterByName($filter_name) {
    $result = $this::getResponseJson($this->get("filters?name={$filter_name}"));
    if ($result['success'] == 1) {
      return $result['data'];
    }

    return NULL;
  }

  /**
   * List all filters in the subscription.
   *
   * @return array
   *   An array of all filters in the subscription.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   * @throws \Exception
   */
  public function listFilters() {
    return $this::getResponseJson($this->get('filters'));
  }

  /**
   * Puts a Filter into Content Hub.
   *
   * @param string|array $query
   *   The query to add to the filter.
   * @param string $name
   *   The name of the filter.
   * @param string $uuid
   *   The filter UUID to update existing filter, NULL to create a new one.
   * @param array $metadata
   *   The Metadata array, empty if not given.
   *
   * @return array
   *   An array of data including the filter UUID, if succeeds.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   * @throws \Exception
   */
  public function putFilter($query, $name, $uuid = NULL, array $metadata = []) {
    $data = [
      'name' => $name,
      'data' => [
        'query' => $query,
      ],
      'metadata' => (object) $metadata,
    ];
    if (!empty($uuid)) {
      $data['uuid'] = $uuid;
    }
    $options = ['body' => json_encode($data)];

    return self::getResponseJson($this->put('filters', $options));
  }

  /**
   * Deletes a filter, given its UUID.
   *
   * @param string $filter_uuid
   *   The filter UUID.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function deleteFilter($filter_uuid) {
    return $this->delete("filters/{$filter_uuid}");
  }

  /**
   * List all filters attached to a particular webhook.
   *
   * @param string $webhook_id
   *   The webhook UUID.
   *
   * @return array
   *   An array of data including the filter UUID, if succeeds.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   * @throws \Exception
   */
  public function listFiltersForWebhook($webhook_id) {
    return $this::getResponseJson($this->get("settings/webhooks/$webhook_id/filters"));
  }

  /**
   * Attaches a filter to a webhook.
   *
   * @param string $filter_id
   *   The filter UUID.
   * @param string $webhook_id
   *   The Webhook UUID.
   *
   * @return array
   *   An array of data including the filter UUID, if succeeds.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   * @throws \Exception
   */
  public function addFilterToWebhook($filter_id, $webhook_id) {
    $data = ['filter_id' => $filter_id];
    $options = ['body' => json_encode($data)];

    return self::getResponseJson($this->post("settings/webhooks/$webhook_id/filters", $options));
  }

  /**
   * Detaches filter from webhook.
   *
   * @param string $filter_id
   *   Filter UUID.
   * @param string $webhook_id
   *   Webhook UUID.
   *
   * @return mixed
   *   Response.
   *
   * @throws \Exception
   */
  public function removeFilterFromWebhook($filter_id, $webhook_id) {
    $options = ['body' => json_encode(['filter_id' => $filter_id])];
    $response = $this->delete("settings/webhooks/$webhook_id/filters", $options);

    return self::getResponseJson($response);
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

      $args = $this->addSearchCriteriaHeader($args);

      return parent::__call($method, $args);
    }
    catch (Exception $e) {
      $exceptionResponse = $this->getExceptionResponse($method, $args, $e);
    }
    return $exceptionResponse;
  }

  /**
   * Obtains the appropriate exception Response.
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
    $api_call = $args[0];
    $response = $exception->getResponse();
    $response_body = json_decode($exception->getResponse()->getBody(), TRUE);
    $code = $response_body['error']['code'];
    $message = $response_body['error']['message'];
    $reason = sprintf("Request ID: %s, Method: %s, Path: %s, Status Code: %s, Reason: %s, Error Code: %s, Error Message: \"%s\"",
      $response_body['request_id'],
      strtoupper($method),
      $api_call,
      $exception->getResponse()->getStatusCode(),
      $exception->getResponse()->getReasonPhrase(),
      $code,
      $message,
    );

    # Here decide what type of error/warning we want to show.
    $this->logger->error($reason);

    // Return the response.
    return $response;
  }

  /**
   * Obtains the appropriate exception message for the selected exception.
   *
   * This is the place to set up exception messages per request.
   *
   * @param string $method
   *   The Request to Plexus, as defined in the content-hub-php library.
   * @param array $args
   *   The Request arguments.
   * @param \Exception $exception
   *   The Exception object.
   *
   * @return ResponseInterface The text to write in the messages.
   * The text to write in the messages.
   *
   *  @codeCoverageIgnore
   */
  protected function getExceptionMessage($method, array $args, \Exception $exception)
  {
    $response = NULL;
    $status_code = HttpResponse::HTTP_INTERNAL_SERVER_ERROR;
    $error_details = $exception->getMessage();

    if ($exception instanceof ServerException) {
      return $this->getErrorResponse($status_code,
        sprintf('Could not reach the Content Hub. Please verify your hostname and Credentials. [Error message: %s]',
          $error_details));
    }
    if ($exception instanceof ConnectException) {
      return $this->getErrorResponse($status_code,
        sprintf('Could not reach the Content Hub. Please verify your hostname URL. [Error message: %s]',
          $error_details));
    }
    if ($exception instanceof ClientException || $exception instanceof BadResponseException) {
      $response = $exception->getResponse();
      $status_code = $response->getStatusCode();
      $error_details = $response->getReasonPhrase();
    }

    switch ($method) {
      case 'getClientByName':
        // All good, means the client name is available.
        if ($response && HttpResponse::HTTP_NOT_FOUND === $status_code) {
          return $response;
        }

        $message = sprintf('Error trying to connect to the Content Hub" (Error Code = %d: %s)',
          $status_code, $error_details);
        break;

      case 'addWebhook':
        $message = sprintf('There was a problem trying to register Webhook URL = %s. Please try again. (Error Code = %d: %s)',
          $args[0], $status_code, $error_details);
        break;

      case 'deleteWebhook':
        // This function only requires one argument (webhook_uuid), but
        // we are using the second one to pass the webhook_url.
        $webhook_url = $args[1] ?? $args[0];
        $message = sprintf('There was a problem trying to unregister Webhook URL = %s. Please try again. (Error Code = %d: @%s)',
          $webhook_url, $status_code, $error_details);
        break;

      case 'purge':
        $message = sprintf('Error purging entities from the Content Hub [Error Code = %d: %s]',
          $status_code, $error_details);
        break;

      case 'readEntity':
        $message = sprintf('Error reading entity with UUID="%s" from Content Hub (Error Code = %d: %s)', $args[0],
          $status_code, $error_details);
        break;

      case 'createEntity':
        $message = sprintf('Error trying to create an entity in Content Hub (Error Code = %d: %s)',
          $status_code, $error_details);
        break;

      case 'createEntities':
        $message = sprintf('Error trying to create entities in Content Hub (Error Code = %d: %s)',
          $status_code, $error_details);
        break;

      case 'updateEntity':
        $message = sprintf('Error trying to update an entity with UUID="%s" in Content Hub (Error Code = %d: %s)', $args[1],
          $status_code, $error_details);
        break;

      case 'updateEntities':
        $message = sprintf('Error trying to update some entities in Content Hub (Error Code = %d: %s)',
          $status_code, $error_details);
        break;

      case 'deleteEntity':
        $message = sprintf('Error trying to delete entity with UUID="%s" in Content Hub (Error Code = %d: %s)',
          $args[0], $status_code, $error_details);
        break;

      case 'searchEntity':
        $message = sprintf('Error trying to make a search query to Content Hub. Are your credentials inserted correctly? (Error Code = %d: %s)',
          $status_code, $error_details);
        break;

      case 'addEntitiesToInterestList':
        $message = sprintf('Error trying to add entities to the interest list (Error Code = %d: %s)',
          $status_code, $error_details);
        break;

      case 'deleteInterest':
        $message = sprintf('Error trying to remove entity from the interest list (Error Code = %d: %s)',
          $status_code, $error_details);
        break;

      default:
        $message = sprintf('Error trying to connect to the Content Hub (Error Code = %d: %s)',
          $status_code, $error_details);
    }

    return $this->getErrorResponse($status_code, $message);
  }

  /**
   * Returns error response.
   *
   * @param int $code
   *   Status code.
   * @param string $reason
   *   Reason.
   *
   * @return \GuzzleHttp\Psr7\Response
   *   Response.
   */
  protected function getErrorResponse($code, $reason) {
    return new Response($code, [], json_encode([]), '1.1', $reason);
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

  /**
   * Appends search criteria header.
   *
   * @param array $args
   *   Method arguments.
   *
   * @return array
   *   Processed arguments.
   */
  protected function addSearchCriteriaHeader(array $args) {
    $result = explode('?', $args[0] ?? '');
    if (count($result) < 2) {
      return $args;
    }
    [, $queryString] = $result;
    if (empty($queryString)) {
      return $args;
    }
    parse_str($queryString, $parsedQueryString);

    $languages = $this->getConfig(self::OPTION_NAME_LANGUAGES);
    if (!empty($languages) && is_array($languages)) {
      $parsedQueryString['languages'] = $languages;
    }

    /** @var \Acquia\ContentHubClient\SearchCriteria\SearchCriteria $criteria */
    $criteria = SearchCriteriaBuilder::createFromArray($parsedQueryString);
    $args[1]['headers'] = $args[1]['headers'] ?? [];
    $args[1]['headers'][SearchCriteria::HEADER_NAME] = base64_encode(json_encode($criteria));

    return $args;
  }

}
