<?php

namespace Acquia\ContentHubClient;

use Acquia\ContentHubClient\CDF\CDFObject;
use Acquia\ContentHubClient\Event\GetCDFTypeEvent;
use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ContentHubClient.
 *
 * @package Acquia\ContentHubClient
 */
class ContentHubClient extends Client
{
    // Override VERSION inherited from GuzzleHttp::ClientInterface
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
     * @var EventDispatcherInterface $dispatcher
     */
    protected $dispatcher;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config = [], LoggerInterface $logger, Settings $settings, HmacAuthMiddleware $middleware, EventDispatcherInterface $dispatcher, $api_version = 'v2')
    {
        $this->logger = $logger;
        $this->settings = $settings;
        $this->dispatcher = $dispatcher;

        // "base_url" parameter changed to "base_uri" in Guzzle6, so the following line
        // is there to make sure it does not disrupt previous configuration.
        if (!isset($config['base_uri']) && isset($config['base_url'])) {
            $config['base_uri'] = self::makeBaseURL($config['base_url'], $api_version);
        } else {
            $config['base_uri'] = self::makeBaseURL($config['base_uri'], $api_version);
        }

        // Setting up the User Header string.
        $user_agent_string = self::LIBRARYNAME . '/' . self::VERSION . ' ' . \GuzzleHttp\default_user_agent();
        if (isset($config['client-user-agent'])) {
            $user_agent_string = $config['client-user-agent'] . ' ' . $user_agent_string;
        }

        // Setting up the headers.
        $config['headers']['Content-Type'] = 'application/json';
        $config['headers']['X-Acquia-Plexus-Client-Id'] = $settings->getUuid();
        $config['headers']['User-Agent'] = $user_agent_string;

        // Add the authentication handler
        // @see https://github.com/acquia/http-hmac-spec
        if (!isset($config['handler'])) {
            $config['handler'] = HandlerStack::create();
        }
        $config['handler']->push($middleware);

        parent::__construct($config);
    }

    /**
     * Pings the service to ensure that it is available.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \Exception
     *
     * @since 0.2.0
     */
    public function ping()
    {
        $config = $this->getConfig();
        // Create a new client because ping is not behind hmac.
        $client = new Client(['base_uri' => self::makeBaseURL($config['base_url'])]);
        return $this->getResponseJson($client->get('ping'));
    }

    /**
     * Discoverability of the API
     *
     * @param string $endpoint
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function definition($endpoint = '')
    {
        return $this->getResponseJson($this->request('options', $endpoint));
    }

    /**
     * Registers a new client for the active subscription.
     *
     * This method also returns the UUID for the new client being registered.
     *
     * @param string $name
     *   The human-readable name for the client.
     *
     * @return \Acquia\ContentHubClient\ContentHubClient
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public static function register(LoggerInterface $logger, EventDispatcherInterface $dispatcher, $name, $url, $api_key, $secret, $api_version = 'v2')
    {
        $config = [
            'base_uri' => self::makeBaseURL($url, $api_version),
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => self::LIBRARYNAME . '/' . self::VERSION . ' ' . \GuzzleHttp\default_user_agent(),
            ],
            'handler' => HandlerStack::create(),
        ];

        // Add the authentication handler
        // @see https://github.com/acquia/http-hmac-spec
        $key = new Key($api_key, $secret);
        $middleware = new HmacAuthMiddleware($key);
        $config['handler']->push($middleware);
        $client = new Client($config);
        $options['body'] = json_encode(['name' => $name]);
        try {
            $response = $client->post('register', $options);
            $values = self::getResponseJson($response);
            $settings = new Settings($values['name'], $values['uuid'], $api_key, $secret, $url);
            $config = [
                'base_url' => $settings->getUrl()
            ];
            $client = new static($config, $logger, $settings, $settings->getMiddleware(), $dispatcher);
            // @todo remove this once shared secret is returned on the register
            // endpoint.
            // We need the shared secret to be fully functional, so an additional
            // request is required to get that.
            $remote = $client->getRemoteSettings();
            // Now that we have the shared secret, reinstantiate everything and
            // return a new instance of this class.
            $settings = new Settings($settings->getName(), $settings->getUuid(), $settings->getApiKey(), $settings->getSecretKey(), $settings->getUrl(), $remote['shared_secret']);
            return new static($config, $logger, $settings, $settings->getMiddleware(), $dispatcher);
        }
        catch (\Exception $exception) {
            if ($exception instanceof ClientException || $exception instanceof BadResponseException) {
              $message = sprintf('Error registering client with name="%s" (Error Code = %d: %s)', $name, $exception->getResponse()->getStatusCode(), $exception->getResponse()->getReasonPhrase());
              $logger->error($message);
              throw new RequestException($message, $exception->getRequest(), $exception->getResponse());
            }
            if ($exception instanceof RequestException) {
              $message = sprintf('Could not get authorization from Content Hub to register client %s. Are your credentials inserted correctly? (Error message = %s)', $name, $exception->getMessage());
              $logger->error($message);
              throw new RequestException($message, $exception->getRequest(), $exception->getResponse());
            }
            $message = sprintf("An unknown exception was caught. Message: %s", $exception->getMessage());
            $logger->error($message);
            throw new \Exception($message);
        }
    }

    /**
     * Checks Plexus to see if the client name is already in use.
     *
     * @param $name
     * @param $url
     * @param $api_key
     * @param $secret
     * @param string $api_version
     *
     * @return boolean
     *   Whether the clientName from the request matches the name passed to it.
     */
    public static function clientNameExists($name, $url, $api_key, $secret, $api_version = 'v2')
    {
        $config = [
            'base_uri' => self::makeBaseURL($url, $api_version),
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => self::LIBRARYNAME . '/' . self::VERSION . ' ' . \GuzzleHttp\default_user_agent(),
            ],
            'handler' => HandlerStack::create(),
        ];

        // Add the authentication handler
        // @see https://github.com/acquia/http-hmac-spec
        $key = new Key($api_key, $secret);
        $middleware = new HmacAuthMiddleware($key);
        $config['handler']->push($middleware);
        $client = new Client($config);
        $options['body'] = json_encode(['name' => $name]);
        // Attempt to fetch the client name, if it works
        try {
            $client->get("settings/clients/$name");

            return true;
        } catch (\GuzzleHttp\Exception\ClientException $error) {
            return $error->getResponse()->getStatusCode() != 404;
        }
    }

  /**
   * Sends request to asynchronously create entities.
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject[] $objects
   *   Individual CDFObjects to send to ContentHub.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *
   */
    public function createEntities(CDFObject ...$objects)
    {
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
     * @param  string $uuid
     *
     * @return \Acquia\ContentHubClient\CDF\CDFObjectInterface|array
     *   A CDFObject representing the entity or an array if there was no data.
     *
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \ReflectionException
     *
     * @todo can we return a CDFObject here?
     */
    public function getEntity($uuid)
    {
        $return = $this->getResponseJson($this->get("entities/$uuid"));
        if (!empty($return['data']['data'])) {
            return $this->getCDFObject($return['data']['data']);
        }

        return $return;
    }

    /**
     * Searches for entities.
     *
     * @param  array $uuids
     *   An array of UUIDs.
     *
     * @return \Acquia\ContentHubClient\CDFDocument
     *
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \ReflectionException
     */
    public function getEntities(array $uuids)
    {
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
            $options['body'] = json_encode((array)$query);
            $results = $this->getResponseJson($this->get('_search', $options));
            if (is_array($results) && isset($results['hits']['total']) && $results['hits']['total'] > 0) {
                foreach ($results['hits']['hits'] as $key => $item) {
                    $objects[] = $this->getCDFObject($item['_source']['data']);
                }
            }
        }
        $document = new CDFDocument(...$objects);

        return $document;
    }

    /**
     * Retrieves a CDF Object
     *
     * @param $data
     *
     * @return \Acquia\ContentHubClient\CDF\CDFObjectInterface
     * @throws \ReflectionException
     */
    protected function getCDFObject($data)
    {
        $event = new GetCDFTypeEvent($data);
        $this->dispatcher->dispatch(ContentHubLibraryEvents::GET_CDF_CLASS, $event);

        return $event->getObject();
    }

  /**
   * Updates an entity asynchronously.
   *
   * The entity does not need to be passed to this method, but only the resource URL.
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject $object
   *   The CDFObject
   *
   * @return \Psr\Http\Message\ResponseInterface
   *
   */
    public function putEntity(CDFObject $object)
    {
        $options['body'] = json_encode(['entities' => [$object->toArray()], 'resource' => ""]);
        return $this->put("entities/{$object->getUuid()}", $options);
    }

  /**
   * Updates many entities asynchronously.
   *
   * @param \Acquia\ContentHubClient\CDF\CDFObject[] $objects
   *   The CDFObjects to update.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *
   */
    public function putEntities(CDFObject ...$objects)
    {
        $json = [
          'resource' => "",
        ];
        foreach ($objects as $object) {
            $json['data']['entities'][] = $object->toArray();
        }
        $options['body'] = json_encode($json);

        return $this->put('entities', $options);
    }

    /**
     * @param \Acquia\ContentHubClient\CDF\CDFObject ...$objects
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function postEntities(CDFObject ...$objects)
    {
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
     * @param  string                                 $uuid
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function deleteEntity($uuid)
    {
        return $this->delete("entities/$uuid");
    }

  /**
   * Deletes an entity from a webhook's interest list.
   *
   * @param  string                                 $uuid
   * @param  string                                 $webhook_uuid
   *
   * @return \Psr\Http\Message\ResponseInterface
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
     *
     * @throws \Exception
     */
    public function purge()
    {
        return $this->getResponseJson($this->post('entities/purge'));
    }

    /**
     * Restores the state of entities before the previous purge.
     *
     * Only to be used if a purge has been called previously. This means new
     * entities added after the purge was enacted will be overwritten by the
     * previous state. Be VERY careful when using this endpoint.
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function restore()
    {
        return $this->getResponseJson($this->post('entities/restore'));
    }

    /**
     * Reindex a subscription.
     *
     * Schedules a reindex process.
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \Exception
     */
    public function reindex()
    {
        return $this->getResponseJson($this->post('reindex'));
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
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function logs($query = '', $query_options = [])
    {
        $query_options = $query_options + [
            'size' => 20,
            'from' => 0,
            'sort' => 'timestamp:desc'
        ];
        $options['body'] = empty($query) ? '{"query": {"match_all": {}}}' : $query;
        $endpoint = "history?size={$query_options['size']}&from={$query_options['from']}&sort={$query_options['sort']}";
        $response = $this->post($endpoint, $options);
        return $this->getResponseJson($response);
    }

    /**
     * Retrieves active ElasticSearch mapping of entities.
     *
     * @return mixed
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function mapping()
    {
        return $this->getResponseJson($this->get('_mapping'));
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
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \Exception
     */
    public function listEntities($options = [])
    {
        $variables = $options + [
            'limit' => 1000,
            'start' => 0,
            'filters' => [],
        ];

        $url = "entities?limit={$variables['limit']}&start={$variables['start']}";

        $url .= isset($variables['type']) ? "&type={$variables['type']}" :'';
        $url .= isset($variables['origin']) ? "&origin={$variables['origin']}" :'';
        $url .= isset($variables['language']) ? "&language={$variables['language']}" :'';
        $url .= isset($variables['fields']) ? "&fields={$variables['fields']}" :'';
        foreach ($variables['filters'] as $name => $value) {
            $filter = 'filter_' . $name;
            $variables[$filter] = $value;
            $url .= isset($value) ? sprintf('&filter:%s=%s', $name, $value) : '';
        }

        // Now make the request.
        return $this->getResponseJson($this->get($url));
    }

    /**
     * Searches for entities.
     *
     * @param  array $query
     *
     * @return mixed
     *
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function searchEntity($query)
    {
        $options['body'] = json_encode((array) $query);
        return $this->getResponseJson($this->get('_search', $options));
    }

    /**
     * Returns the Client, given the site name.
     *
     * @param string $name
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getClientByName($name)
    {
        return $this->getResponseJson($this->get("settings/clients/$name"));
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getClients()
    {
        $data = $this->getResponseJson($this->get('settings'));

        return $data['clients'];
    }

    /**
     * @return mixed
     *
     * @throws \Exception
     */
    public function getWebHooks()
    {
        $data = $this->getResponseJson($this->get('settings'));

        return $data['webhooks'];
    }

    /**
     * @param $url
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getWebHook($url)
    {
        $webhooks = $this->getWebHooks();
        foreach ($webhooks as $webhook) {
            if ($webhook['url'] == $url) {
                return $webhook;
            }
        }
        return [];
    }

    /**
     * Get the settings that were used to instantiate this client.
     *
     * @return \Acquia\ContentHubClient\Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Obtains the Settings for the active subscription.
     *
     * @return Settings
     *
     * @throws \Exception
     */
    public function getRemoteSettings()
    {
        return $this->getResponseJson($this->get('settings'));
    }

    /**
     * Adds a webhook to the active subscription.
     *
     * @param $webhook_url
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function addWebhook($webhook_url)
    {
        $options['body'] = json_encode(['url' => $webhook_url, 'version' => 2.0]);

        return $this->getResponseJson($this->post('settings/webhooks', $options));
    }

    /**
     * Deletes a webhook from the active subscription.
     *
     * @param $uuid
     *   The UUID of the webhook to delete
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function deleteWebhook($uuid)
    {
        return $this->delete("settings/webhooks/$uuid");
    }

    /**
     * Updates a webhook from the active subscription.
     *
     * @param $uuid
     *   The UUID of the webhook to update
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function updateWebhook($uuid, $url)
    {
        $options['body'] = json_encode(['url' => $url]);

        return $this->put("settings/webhooks/$uuid", $options);
    }

    /**
     * Add entities to Intrest List.
     *
     * @param string $webhook_uuid
     *   The UUID of the webhook
     * @param array $uuids
     *   Entity UUIDs to add to Interest List
     *
     * @return \Psr\Http\Message\ResponseInterface
     *   The response.
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function addEntitiesToInterestList($webhook_uuid, $uuids)
    {
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
     *
     * @throws \Exception
     */
    public function deleteClient($client_uuid = null)
    {
        $settings = $this->getSettings();
        $uuid = $client_uuid ?? $settings->getUuid();
        if (!$this->deleteEntity($uuid)) {
          throw new \Exception(sprintf("Entity with UUID = %s cannot be deleted.", $uuid));
        }
        return $this->delete("settings/client/uuid/$uuid");
    }

    /**
     * Updates a client from the active subscription.
     *
     * @param $uuid
     *   The UUID of the client to update.
     * @param $name
     *   The new name for the client we're updating.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function updateClient($uuid, $name)
    {
        $options['body'] = json_encode(['name' => $name]);
        return $this->put("settings/client/uuid/$uuid", $options) ;
    }

    /**
     * Regenerates a Shared Secret for the Subscription.
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \Exception
     */
    public function regenerateSharedSecret()
    {
        return $this->getResponseJson($this->post('settings/secret', ['body' => json_encode([])]));
    }

    /**
     * Gets filter by UUID.
     *
     * @param string $filter_id
     *   The filter UUID.
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \Exception
     */
    public function getFilter($filter_id)
    {
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
    public function getFilterByName($filter_name)
    {
        $result = $this::getResponseJson($this->get("filters?name={$filter_name}"));
        if ($result['success'] == 1) {
            return $result['data'];
        }

        return null;
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
    public function listFilters()
    {
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
     *   The Metadata array, NULL if not provided.
     *
     * @return array
     *   An array of data including the filter UUID, if succeeds.
     *
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \Exception
     *
     */
    public function putFilter($query, $name = '', $uuid = NULL, $metadata = NULL)
    {
        $data = [
          'name' => $name,
          'data' => [
            'query' => $query,
          ],
        ];
        if (!empty($uuid)) {
          $data['uuid'] = $uuid;
        }
        if (!empty($metadata)) {
          $data['metadata'] = $metadata;
        }
        $options = ['body' => json_encode($data)];

        return $this->getResponseJson($this->put('filters', $options));
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
    public function deleteFilter($filter_uuid)
    {
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
     *
     */
    public function listFiltersForWebhook($webhook_id)
    {
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
    public function addFilterToWebhook($filter_id, $webhook_id)
    {
        $data = ['filter_id' => $filter_id];
        $options = ['body' => json_encode($data)];

        return $this->getResponseJson($this->post("settings/webhooks/$webhook_id/filters", $options));
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
     * @throws \Exception
     */
    public function removeFilterFromWebhook($filter_id, $webhook_id)
    {
        $options = ['body' => json_encode(['filter_id' => $filter_id])];
        $response = $this->delete("settings/webhooks/$webhook_id/filters", $options);
        return $this->getResponseJson($response);
    }

    /**
     * Gets a Json Response from a request.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return mixed
     * @throws \Exception
     */
    public static function getResponseJson(ResponseInterface $response)
    {
        $body = (string) $response->getBody();

        return json_decode($body, true);
    }

    /**
     * {@inheritdoc}
     */
    public function __call($method, $args)
    {
        try {
            if (strpos($args[0], '?')) {
                list($uri, $query) = explode('?', $args[0]);
                $parts = explode('/', $uri);
                if ($query) {
                    $last = array_pop($parts);
                    $last .= "?$query";
                    $parts[] = $last;
                }
            } else {
                $parts = explode('/', $args[0]);
            }
            $args[0] = self::makePath(...$parts);

            return parent::__call($method, $args);
        } catch (\Exception $e) {
            $exceptionResponse = $this->getExceptionMessage($method, $args, $e);
        }
        $this->logger->error((string)$exceptionResponse->getReasonPhrase());

        return $exceptionResponse;
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
   */
    protected function getExceptionMessage($method, array $args, \Exception $exception)
    {
        if ($exception instanceof ServerException) {
            return $this->getErrorResponse(500,
              sprintf('Could not reach the Content Hub. Please verify your hostname and Credentials. [Error message: %s]',
                $exception->getMessage()));
        }
        if ($exception instanceof ConnectException) {
            return $this->getErrorResponse(500,
              sprintf('Could not reach the Content Hub. Please verify your hostname URL. [Error message: %s]',
                $exception->getMessage()));
        }
        if ($exception instanceof ClientException || $exception instanceof BadResponseException) {
            $response = $exception->getResponse();
            switch ($method) {
                case 'getClientByName':
                    // All good, means the client name is available.
                    if ($response->getStatusCode() == 404) {
                        return $response;
                    }

                    return $this->getErrorResponse($response->getStatusCode(),
                      sprintf('Error trying to connect to the Content Hub" (Error Code = %d: %s)', $response->getStatusCode(),
                        $response->getReasonPhrase()));

                case 'addWebhook':
                    return $this->getErrorResponse($response->getStatusCode(),
                      sprintf('There was a problem trying to register Webhook URL = %s. Please try again. (Error Code = %d: %s)',
                        $args[0], $response->getStatusCode(), $response->getReasonPhrase()));

                case 'deleteWebhook':
                    // This function only requires one argument (webhook_uuid), but
                    // we are using the second one to pass the webhook_url.
                    $webhook_url = isset($args[1]) ? $args[1] : $args[0];

                    return $this->getErrorResponse($response->getStatusCode(),
                      sprintf('There was a problem trying to <b>unregister</b> Webhook URL = %s. Please try again. (Error Code = %d: @%s)',
                        $webhook_url, $response->getStatusCode(), $response->getReasonPhrase()));

                case 'purge':
                    return $this->getErrorResponse($response->getStatusCode(),
                      sprintf('Error purging entities from the Content Hub [Error Code = %d: %s]', $response->getStatusCode(),
                        $response->getReasonPhrase()));

                case 'readEntity':
                    return $this->getErrorResponse($response->getStatusCode(),
                      sprintf('Error reading entity with UUID="%s" from Content Hub (Error Code = %d: %s)', $args[0],
                        $response->getStatusCode(), $response->getReasonPhrase()));

                case 'createEntity':
                    return $this->getErrorResponse($response->getStatusCode(),
                      sprintf('Error trying to create an entity in Content Hub (Error Code = %d: %s)', $response->getStatusCode(),
                        $response->getReasonPhrase()));

                case 'createEntities':
                    return $this->getErrorResponse($response->getStatusCode(),
                      sprintf('Error trying to create entities in Content Hub (Error Code = %d: %s)', $response->getStatusCode(),
                        $response->getReasonPhrase()));

                case 'updateEntity':
                    return $this->getErrorResponse($response->getStatusCode(),
                      sprintf('Error trying to update an entity with UUID="%s" in Content Hub (Error Code = %d: %s)', $args[1],
                        $response->getStatusCode(), $response->getReasonPhrase()));

                case 'updateEntities':
                    return $this->getErrorResponse($response->getStatusCode(),
                      sprintf('Error trying to update some entities in Content Hub (Error Code = %d: %s)',
                        $response->getStatusCode(), $response->getReasonPhrase()));

                case 'deleteEntity':
                    return $this->getErrorResponse($response->getStatusCode(),
                      sprintf('Error trying to delete entity with UUID="@uuid" in Content Hub (Error Code = @error_code: @error_message)',
                        $args[0], $response->getStatusCode(), $response->getReasonPhrase()));

                case 'searchEntity':
                    return $this->getErrorResponse($response->getStatusCode(),
                      sprintf('Error trying to make a search query to Content Hub. Are your credentials inserted correctly? (Error Code = %d: %s)',
                        $response->getStatusCode(), $response->getReasonPhrase()));

                default:
                    return $response;
            }
        }
        if ($exception instanceof RequestException) {
            switch ($method) {
                // Customize the error message per request here.
                case 'createEntity':
                    return $this->getErrorResponse(500,
                      sprintf('Error trying to create an entity in Content Hub (Error Message: %s)', $exception->getMessage()));

                case 'createEntities':
                    return $this->getErrorResponse(500,
                      sprintf('Error trying to create entities in Content Hub (Error Message = %s)', $exception->getMessage()));

                case 'updateEntity':
                    return $this->getErrorResponse(500,
                      sprintf('Error trying to update entity with UUID="%s" in Content Hub (Error Message = %s)', $args[1],
                        $exception->getMessage()));

                case 'updateEntities':
                    return $this->getErrorResponse(500,
                      sprintf('Error trying to update some entities in Content Hub (Error Message = %s)',
                        $exception->getMessage()));

                case 'deleteEntity':
                    return $this->getErrorResponse(500,
                      sprintf('Error trying to delete entity with UUID="%s" in Content Hub (Error Message = %s)', $args[0],
                        $exception->getMessage()));

                case 'searchEntity':
                    return $this->getErrorResponse(500,
                      sprintf('Error trying to make a search query to Content Hub. Are your credentials inserted correctly? (Error Message = %s)',
                        $exception->getMessage()));

                default:
                    return $this->getErrorResponse(500,
                      sprintf('Error trying to connect to the Content Hub. Are your credentials inserted correctly? (Error Message = %s)',
                        $exception->getMessage()));
            }
        }

        return $this->getErrorResponse(500,
          sprintf('Error trying to connect to the Content Hub (Error Message = %s)', $exception->getMessage()));
    }

    protected function getErrorResponse($code, $reason)
    {
        return new Response($code, [], json_encode([]), '1.1', $reason);
    }

    /**
     * Make a base url out of components and add a trailing slash to it
     *
     * @param string[] $base_url_components
     *
     * @return string
     */
    protected static function makeBaseURL(...$base_url_components): string
    {
        return self::gluePartsTogether($base_url_components, '/').'/';
    }

    /**
     * Make path out of its individual components
     *
     * @param string[] $path_components
     *
     * @return string
     */
    protected static function makePath(...$path_components): string
    {
        return self::gluePartsTogether($path_components, '/');
    }

    /**
     * Glue all elements of an array together
     *
     * @param array $parts
     * @param string $glue
     *
     * @return string
     */
    protected static function gluePartsTogether(array $parts, string $glue): string
    {
        return implode($glue, self::removeAllLeadingAndTrailingSlashes($parts));
    }

    /**
     * Strip all leading and trailing slashes from all components of the given array
     *
     * @param string[] $components
     *
     * @return string[]
     */
    protected static function removeAllLeadingAndTrailingSlashes(array $components): array
    {
        return array_map(function ($component) {
            return trim($component, '/');
        }, $components);
    }
}
