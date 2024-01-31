<?php

namespace Acquia\ContentHubClient;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\MetaData\ClientMetaData;
use Acquia\ContentHubClient\SearchCriteria\SearchCriteria;
use Acquia\ContentHubClient\SearchCriteria\SearchCriteriaBuilder;
use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ContentHubClient.
 *
 * @package Acquia\ContentHubClient
 */
class ContentHubClient implements ClientInterface {

  use ContentHubClientTrait;

  const OPTION_NAME_LANGUAGES = 'client-languages';

  const FEATURE_DEPRECATED_RESPONSE = [
    'success' => FALSE,
    'error' => [
      'code' => HttpResponse::HTTP_GONE,
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
   * The Event Dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * Cached remote settings.
   *
   * @var array
   */
  protected $remoteSettings = [];

  /**
   * Whether to return cached remote settings.
   *
   * @var bool
   *   True if it should return cached.
   */
  protected $shouldReturnCachedRemoteSettings = FALSE;

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
    array $config = [],
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
    $user_agent_string = ContentHubDescriptor::userAgent();
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

    $this->httpClient = ObjectFactory::getGuzzleClient($config);
    $this->setConfigs($config);
  }
  // phpcs:enable

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
   *
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
   * @param \Acquia\ContentHubClient\MetaData\ClientMetaData $client_metadata
   *   Client metadata.
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
    string $name,
    string $url,
    string $api_key,
    string $secret,
    ClientMetaData $client_metadata,
    string $api_version = 'v2'
  ) {
    $config = [
      'base_uri' => self::makeBaseURL($url, $api_version),
      'headers' => [
        'Content-Type' => 'application/json',
        'User-Agent' => ContentHubDescriptor::userAgent(),
      ],
      'handler' => ObjectFactory::getHandlerStack(),
    ];

    // Add the authentication handler.
    // @see https://github.com/acquia/http-hmac-spec
    $key = ObjectFactory::getAuthenticationKey($api_key, $secret);
    $middleware = ObjectFactory::getHmacAuthMiddleware($key);
    $config['handler']->push($middleware);
    $client = ObjectFactory::getGuzzleClient($config);
    $body = [
      'name' => $name,
      'metadata' => $client_metadata->toArray(),
    ];
    $options['body'] = json_encode($body);
    try {
      $response = $client->post('register', $options);
      $values = self::getResponseJson($response);
      $settings = ObjectFactory::instantiateSettings($values['name'],
        $values['uuid'], $api_key, $secret, $url);
      $config = [
        'base_url' => $settings->getUrl(),
      ];
      $client = ObjectFactory::getCHClient($logger, $settings,
        $settings->getMiddleware(), $dispatcher, $config);
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
      return ObjectFactory::getCHClient($logger, $settings,
        $settings->getMiddleware(), $dispatcher, $config);
    }
    catch (\Exception $exception) {
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
      throw new \Exception($message);
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
        'User-Agent' => ContentHubDescriptor::userAgent(),
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
      if (!isset($results['hits'])) {
        throw new \RuntimeException('Content Hub Search endpoint is not reachable.');
      }
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
    $this->dispatcher->dispatch($event, ContentHubLibraryEvents::GET_CDF_CLASS);

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
   * Deletes multiple entity uuids.
   *
   * @param array $uuids
   *   Uuids to delete.
   *
   * @return mixed
   *   Response.
   *
   * @throws \Exception
   */
  public function deleteEntities(array $uuids) {
    $options['body'] = json_encode($uuids);
    return self::getResponseJson($this->delete("entities", $options));
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
      $variables["filter:{$key}"] = $value;
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
    return self::getResponseJson($this->get("settings/client/name/$name"));
  }

  /**
   * Returns the Client, given its uuid.
   *
   * @param string $uuid
   *   Client uuid.
   *
   * @return array
   *   The client array (uuid, name).
   *
   * @throws \Exception
   */
  public function getClientByUuid(string $uuid): array {
    $settings = $this->getRemoteSettings();
    foreach ($settings['clients'] as $client) {
      if ($client['uuid'] === $uuid) {
        return $client;
      }
    }
    return [];
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
   * @return \Acquia\ContentHubClient\Webhook|array
   *   Webhook.
   *
   * @throws \Exception
   *
   * @codeCoverageIgnore
   */
  public function getWebHook(string $url) {
    return current(array_filter($this->getWebHooks(),
        function (Webhook $webhook) use ($url) {
          return $webhook->getUrl() === $url;
        })) ?? [];
  }

  /**
   * Returns status information for all webhooks.
   *
   * @return array
   *   Webhooks status information.
   *
   * @throws \Exception
   */
  public function getWebhookStatus() {
    return self::getResponseJson($this->get('settings/webhooks/status'));
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
  public function addEntitiesToInterestList(string $webhook_uuid, array $uuids): ResponseInterface {
    $options['body'] = json_encode(['interests' => $uuids]);

    return $this->post("interest/webhook/$webhook_uuid", $options);
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
  public function getInterestsByWebhook(string $webhook_uuid): array {
    $data = self::getResponseJson($this->get("interest/webhook/$webhook_uuid"));

    return $data['data']['interests'] ?? [];
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
  public function deleteInterest(string $uuid, string $webhook_uuid): ResponseInterface {
    return $this->delete("interest/$uuid/$webhook_uuid");
  }

  /**
   * Deletes multiple entities from a webhook's interest list.
   *
   * @param string $webhook_uuid
   *   Webhook UUID.
   * @param array $interest_list
   *   An array of interest items.
   * @param string $site_role
   *   Site Role.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   */
  public function deleteMultipleInterest(string $webhook_uuid, array $interest_list, string $site_role): ResponseInterface {
    $options['body'] = json_encode(['uuids' => [$interest_list]]);
    return $this->delete("v2/interest/$webhook_uuid/$site_role", $options);
  }

  /**
   * Returns an extended interest list based on the site role.
   *
   * @param string $webhook_uuid
   *   Identifier of the webhook.
   * @param string $site_role
   *   The role of the site.
   * @param bool|null $disable_syndication
   *   Filter for disabled entities.
   *   If set to true, only disabled entities will be returned.
   *   If set to false, only enabled entities will be returned.
   *   If not set, all the entities will be returned.
   *
   * @return array
   *   An associate array keyed by the entity uuid.
   *
   * @throws \Exception
   */
  public function getInterestsByWebhookAndSiteRole(string $webhook_uuid, string $site_role, ?bool $disable_syndication = NULL): array {
    $options = [];
    if (isset($disable_syndication)) {
      $options['query'] = [
        'disable_syndication' => $disable_syndication,
      ];
    }
    $data = self::getResponseJson($this->get("interest/webhook/$webhook_uuid/$site_role", $options));
    return $data['data'] ?? [];
  }

  /**
   * The extended interest list to add based on site role.
   *
   * Format:
   * [
   *   'fe5f27d1-6e41-4609-b65a-2cb179549d1e' => [
   *     'status' => '',
   *     'reason' => '',
   *     'event_ref' => '',
   *   ],
   * ]
   *
   * @param string $webhook_uuid
   *   The webhook uuid to register interest items for.
   * @param string $site_role
   *   The site role.
   * @param array $interest_list
   *   An array of interest items.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response object.
   */
  public function addEntitiesToInterestListBySiteRole(string $webhook_uuid, string $site_role, array $interest_list): ResponseInterface {
    $options['body'] = json_encode($interest_list);

    return $this->post("interest/webhook/$webhook_uuid/$site_role", $options);
  }

  /**
   * The extended interest list to add based on site role.
   *
   * Format:
   *
   *   @see \Acquia\ContentHubClient\ContentHubClient::addEntitiesToInterestListBySiteRole
   *
   * @param string $webhook_uuid
   *   The webhook uuid to register interest items for.
   * @param string $site_role
   *   The site role.
   * @param array $interest_list
   *   An array of interest items.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response object.
   */
  public function updateInterestListBySiteRole(string $webhook_uuid, string $site_role, array $interest_list): ResponseInterface {
    $options['body'] = json_encode($interest_list);

    return $this->put("interest/webhook/$webhook_uuid/$site_role", $options);
  }

  /**
   * Obtains the Settings for the active subscription.
   *
   * @return array
   *   Response.
   *
   * @throws \Exception
   *
   * @codeCoverageIgnore
   */
  public function getRemoteSettings(): array {
    if ($this->shouldReturnCachedRemoteSettings && !empty($this->remoteSettings)) {
      return $this->remoteSettings;
    }
    $this->remoteSettings = self::getResponseJson($this->get('settings'));
    return !is_array($this->remoteSettings) ? [] : $this->remoteSettings;
  }

  /**
   * Sets cachable remote settings.
   *
   * @param bool $should_cache
   *   If set to true, returns cached remote settings.
   */
  public function cacheRemoteSettings(bool $should_cache): void {
    $this->shouldReturnCachedRemoteSettings = $should_cache;
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
   * @param string $suppress_duration
   *   Duration for which webhook should be suppressed. e.g. 24h.
   *   If not passed, webhook will be indefinitely suppressed.
   *
   * @return mixed
   *   Response body of backend call.
   */
  public function suppressWebhook(string $webhook_uuid, string $suppress_duration = '') {
    $options['body'] = json_encode(['suppress_by' => $suppress_duration]);
    return self::getResponseJson($this->put("webhook/$webhook_uuid/suppress", $options));
  }

  /**
   * Reoriginates entity uuid with new target origin.
   *
   * @param string $entity_uuid
   *   Entity uuid.
   * @param string $target
   *   Origin of target site.
   *
   * @return mixed
   *   API response.
   *
   * @throws \Exception
   */
  public function reoriginateEntity(string $entity_uuid, string $target) {
    $client = $this->getClientByUuid($target);
    if (empty($client)) {
      throw new \RuntimeException(sprintf('%s target client is not registered, please check target origin uuid.', $target));
    }
    $options['body'] = json_encode(['target' => $target]);
    return self::getResponseJson($this->post("entities/$entity_uuid/reoriginate", $options));
  }

  /**
   * Reoriginate all entities from source origin to target origin.
   *
   * @param string $source
   *   Source origin uuid.
   * @param string $target
   *   Target origin uuid.
   *
   * @return mixed
   *   API response.
   *
   * @throws \Exception
   */
  public function reoriginateAllEntities(string $source, string $target) {
    $clients = array_column($this->getClients(), 'uuid');
    if (!in_array($source, $clients, TRUE)) {
      throw new \RuntimeException(sprintf('%s source client is not registered, first register it and then try reorigination to target.', $source));
    }

    if (!in_array($target, $clients, TRUE)) {
      throw new \RuntimeException(sprintf('%s target client is not registered, please check target origin uuid.', $target));
    }
    $options['body'] = json_encode(['origin' => $source, 'target' => $target]);
    return self::getResponseJson($this->post('entities/reoriginate', $options));
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
      throw new \Exception(sprintf("Entity with UUID = %s cannot be deleted.", $uuid));
    }
    if ($response->getStatusCode() !== 404 &&
      ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300)
    ) {
      throw new \Exception(sprintf("Entity with UUID = %s cannot be deleted. Message: %s", $uuid, (string) $response->getBody()));
    }
    return $this->delete("settings/client/uuid/$uuid");
  }

  /**
   * Updates a client from the active subscription.
   *
   * @param string $uuid
   *   The UUID of the client to update.
   * @param string|null $new_name
   *   The new name for the client we're updating.
   * @param \Acquia\ContentHubClient\MetaData\ClientMetaData|null $client_metadata
   *   Client metadata.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response.
   */
  public function updateClient(string $uuid, ?string $new_name = NULL, ?ClientMetaData $client_metadata = NULL) {
    $options = $this->getOptionsForClientUpdate($new_name, $client_metadata);
    return $this->put("settings/client/uuid/$uuid", $options);
  }

  /**
   * Sets options for updating the client.
   *
   * @param string|null $new_name
   *   The new name for the client we're updating.
   * @param \Acquia\ContentHubClient\MetaData\ClientMetaData|null $client_metadata
   *   Client metadata.
   *
   * @return array
   *   Options array for request.
   */
  protected function getOptionsForClientUpdate(?string $new_name, ?ClientMetaData $client_metadata): array {
    if (empty($new_name) && empty($client_metadata)) {
      throw new \RuntimeException('Both new_name and client_metadata are empty. At least one of them is required.');
    }
    $body = [];
    if ($new_name) {
      $body['name'] = $new_name;
    }
    if ($client_metadata) {
      $body['metadata'] = $client_metadata->toArray();
    }
    $options['body'] = json_encode($body);
    return $options;
  }

  /**
   * Updates a client based on name.
   *
   * @param string $current_name
   *   Current client name.
   * @param string|null $new_name
   *   New client name.
   * @param \Acquia\ContentHubClient\MetaData\ClientMetaData|null $client_metadata
   *   Client metadata.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   Response from service.
   */
  public function updateClientByName(string $current_name, ?string $new_name = NULL, ?ClientMetaData $client_metadata = NULL) {
    $options = $this->getOptionsForClientUpdate($new_name, $client_metadata);
    return $this->put("settings/client/name/$current_name", $options);
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

  /**
   * Fetch snapshots.
   *
   * @return mixed
   *   Response.
   *
   * @throws \Exception
   */
  public function getSnapshots() {
    return self::getResponseJson($this->get('snapshots'));
  }

  /**
   * Create a snapshot.
   *
   * @return mixed
   *   Response.
   *
   * @throws \Exception
   */
  public function createSnapshot() {
    return self::getResponseJson($this->post('snapshots'));
  }

  /**
   * Deletes a snapshot.
   *
   * @param string $name
   *   The name of the snapshot.
   *
   * @return mixed
   *   Response from backend call.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function deleteSnapshot($name) {
    return self::getResponseJson($this->delete("snapshots/$name"));
  }

  /**
   * Restore a snapshot.
   *
   * @param string $name
   *   The name of the snapshot.
   *
   * @return mixed
   *   Response from backend call.
   *
   * @throws \GuzzleHttp\Exception\RequestException
   */
  public function restoreSnapshot(string $name) {
    return self::getResponseJson($this->put("snapshots/$name/restore"));
  }

  /**
   * Initiates Scroll API request chain for a particular filter.
   *
   * @param string $filter_uuid
   *   Filter uuid to execute by.
   * @param string|int $scroll_time_window
   *   How long the scroll cursor will be retained inside memory. Must be
   *   suffixed with duration unit (m, s, ms etc.).
   * @param int $size
   *   Amount of entities to return.
   *
   * @return array
   *   Response from backend call.
   *
   * @throws \Exception
   */
  public function startScrollByFilter(string $filter_uuid, $scroll_time_window, int $size): array {
    return self::getResponseJson($this->post("filters/$filter_uuid/scroll", [
      'query' => [
        'scroll' => $scroll_time_window,
        'size' => $size,
      ],
    ]));
  }

  /**
   * Initiates Scroll API request chain.
   *
   * @param string $scroll_time_window
   *   How long the scroll cursor will be retained inside memory. Must be
   *   suffixed with duration unit (m, s, ms etc.).
   * @param int $size
   *   Amount of entities to return.
   * @param array $query
   *   Search query.
   *
   * @return array
   *   Response from scroll API.
   *
   * @throws \Exception
   */
  public function startScroll(string $scroll_time_window = '30m', int $size = 100, array $query = []): array {
    $options['body'] = json_encode($query);
    $options['query'] = [
      'scroll' => $scroll_time_window,
      'size' => $size,
    ];
    return self::getResponseJson($this->post('scroll', $options));
  }

  /**
   * Continue Scroll API request chain.
   *
   * Notice: scroll id is changing continuously once you make a call.
   *
   * @param string $scroll_id
   *   Scroll id.
   * @param string|int $scroll_time_window
   *   How long the scroll cursor will be retained inside memory.
   *
   * @return array
   *   Response from backend call.
   *
   * @throws \Exception
   */
  public function continueScroll(string $scroll_id, $scroll_time_window): array {
    $options = [
      'body' => json_encode([
        'scroll_id' => $scroll_id,
        'scroll' => $scroll_time_window,
      ]),
    ];

    return self::getResponseJson($this->post('scroll/continue', $options));
  }

  /**
   * Cancel Scroll API request chain.
   *
   * @param string $scroll_id
   *   Scroll id.
   *
   * @return array|null
   *   Response from backend call.
   *
   * @throws \Exception
   */
  public function cancelScroll(string $scroll_id): ?array {
    $options = [
      'body' => json_encode([
        'scroll_id' => [$scroll_id],
      ]),
    ];

    return self::getResponseJson($this->delete("scroll", $options));
  }

  /**
   * Fetches entities via query params.
   *
   * @param array $params
   *   Query params.
   *
   * @return array|null
   *   Response from backend call.
   *
   * @throws \Exception
   */
  public function queryEntities(array $params = []): ?array {
    $args = $params ? [RequestOptions::QUERY => $params] : [];
    return self::getResponseJson($this->get('entities', $args));
  }

  /**
   * Checks whether the given account is featured.
   *
   * @return bool
   *   True if the account is featured.
   *
   * @throws \Exception
   */
  public function isFeatured(): bool {
    $remote = $this->getRemoteSettings();
    return $remote['featured'] ?? FALSE;
  }

}
