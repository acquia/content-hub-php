<?php

namespace Acquia\ContentHubClient;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Acquia\ContentHubClient\Data\Adapter;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;


class ContentHub extends Client
{
    // Override VERSION inherited from GuzzleHttp::ClientInterface
    const VERSION = '1.3.3';
    const LIBRARYNAME = 'AcquiaContentHubPHPLib';
    const FEATURE_DEPRECATED_RESPONSE = [
      'success' => FALSE,
      'error' => [
        'code' => SymfonyResponse::HTTP_GONE,
        'message' => 'This feature is deprecated',
      ],
    ];
    private $adapter;

    /**
     * Defines the Content Hub API Version.
     *
     * @var string
     */
    private $api_version;

    /**
     * Overrides \GuzzleHttp\Client::__construct()
     *
     * @param string $origin
     * @param array  $middlewares
     * @param array  $config
     * @param string $api_version
     */
    public function __construct($origin, array $middlewares, array $config = [], $api_version = 'v1')
    {
        // "base_url" parameter changed to "base_uri" in Guzzle6, so the following line
        // is there to make sure it does not disrupt previous configuration.
        if (!isset($config['base_uri']) && isset($config['base_url'])) {
            $config['base_uri'] = $config['base_url'];
        }

        // Setting up the User Header string
        $user_agent_string = $this::LIBRARYNAME . '/' . $this::VERSION . ' ' . \GuzzleHttp\default_user_agent();
        if (isset($config['client-user-agent'])) {
            $user_agent_string = $config['client-user-agent'] . ' ' . $user_agent_string;
        }

        // Setting up the headers.
        $config['headers']['Content-Type'] = 'application/json';
        $config['headers']['X-Acquia-Plexus-Client-Id'] = $origin;
        $config['headers']['User-Agent'] = $user_agent_string;

        // Setting up Adapter Configuration.
        $adapterConfig = isset($config['adapterConfig']) ? $config['adapterConfig'] : [];
        unset($config['adapterConfig']);

        // Set the Adapter.
        $this->adapter = new Adapter($adapterConfig);

        // Define the API Version.
        $this->api_version = $api_version;

        // Add the authentication handler
        // @see https://github.com/acquia/http-hmac-spec
        if (!isset($config['handler'])) {
            $config['handler'] = HandlerStack::create();
        }
        foreach ($middlewares AS $middleware) {
          $config['handler']->push($middleware->getMiddleware());
        }

        parent::__construct($config);
    }

    /**
     * Pings the service to ensure that it is available.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     *
     * @since 0.2.0
     */
    public function ping()
    {
        $endpoint = "/{$this->api_version}/ping";
        return $this->get($endpoint);
    }

    /**
     * Discoverability of the API
     *
     * @param string $endpoint
     *
     * @return array
     */
    public function definition($endpoint = '')
    {
        $request = new Request('OPTIONS', $endpoint);
        return $this->getResponseJson($request);
    }

    /**
     * Registers a new client for the active subscription.
     *
     * This method also returns the UUID for the new client being registered.
     *
     * @param string $name
     *   The human-readable name for the client.
     *
     * @return array
     *   An array of the following format, as an example:
     *    [
     *        'name' => $name,
     *        'uuid' => '11111111-1111-1111-1111-111111111111'
     *    ]
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function register($name)
    {
        $json = [
            'name' => $name,
        ];
        $body = json_encode($json);
        $endpoint = "/{$this->api_version}/register";
        $request = new Request('POST', $endpoint, [], $body);
        return $this->getResponseJson($request);
    }

    /**
     * Sends request to asynchronously create an entity.
     *
     * The entity does not need to be passed to this method, but only the resource URL.
     *
     * @deprecated since 0.6.0
     *
     * @param  string $resource
     *   This string should contain the URL where Plexus can read the entity's CDF.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function createEntity($resource)
    {
      return $this->createEntities($resource);
    }

    /**
     * Sends request to asynchronously create entities.
     *
     * The entity does not need to be passed to this method, but only the resource URL.
     *
     * @param  string $resource
     *   This string should contain the URL where Plexus can read the entities' CDF.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function createEntities($resource)
    {
        $json = [
            'resource' => $resource,
        ];
        $body = json_encode($json);
        $endpoint = "/{$this->api_version}/entities";
        $request = new Request('POST', $endpoint, [], $body);
        $response = $this->send($request);
        return $response;
    }

    /**
     * Sends request to synchronously update entities.
     *
     * The CDF is sent in the payload.
     *
     * @param array $entities
     *   An array of entities.
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function putEntities($entities) {
        $body = json_encode([
          'data' => $entities
        ]);
        $endpoint = "/{$this->api_version}/entities";
        $request = new Request('PUT', $endpoint, [], $body);
        $response = $this->send($request);
        return $response;
    }

    /**
     * Returns an entity by UUID.
     *
     * @param  string                               $uuid
     *
     * @return \Acquia\ContentHubClient\Entity
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function readEntity($uuid)
    {
        $endpoint = "/{$this->api_version}/entities/{$uuid}";
        $request = new Request('GET', $endpoint);
        $data = $this->getResponseJson($request);
        $config = [
            'dataType' => 'Entity',
        ];
        $translatedData = $this->adapter->translate($data['data']['data'], $config);
        return new Entity($translatedData);
    }

    /**
     * Obtains a group of entities, given UUIDs.
     *
     * @param array $uuids
     *   An array of UUIDs.
     *
     * @return \Acquia\ContentHubClient\Entity[]
     *   An array of Content Hub Entity objects keyed by UUID.
     */
    public function readEntities(array $uuids)
    {
        $url = "/{$this->api_version}/_search";
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
            $body = json_encode((array) $query);
            $request = new Request('GET', $url, [], $body);
            $results = $this->getResponseJson($request);
            if (is_array($results) && isset($results['hits']['total']) && $results['hits']['total'] > 0) {
                foreach ($results['hits']['hits'] as $key => $item) {
                    $entity = $item['_source']['data'];
                    $config = [
                        'dataType' => 'Entity',
                    ];
                    $translatedData = $this->adapter->translate($entity, $config);
                    $objects[$entity['uuid']] = new Entity($translatedData);
                }
            }
        }
        return $objects;
    }

    /**
     * Updates an entity asynchronously.
     *
     * The entity does not need to be passed to this method, but only the resource URL.
     *
     * @param  string $resource
     *   This string should contain the URL where Plexus can read the entity's CDF.
     * @param  string $uuid
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function updateEntity($resource, $uuid)
    {
        $json = [
            'resource' => $resource,
        ];
        $body = json_encode($json);
        $endpoint = "/{$this->api_version}/entities/{$uuid}";
        $request = new Request('PUT', $endpoint, [], $body);
        $response = $this->send($request);
        return $response;
    }

    /**
     * Updates many entities asynchronously.
     *
     * The entities do not need to be passed to this method, but only the resource URL
     * to the CDF that contains all entities in json format.
     *
     * @param  string $resource
     *   This string should contain the URL where Plexus can read the entities' CDF.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function updateEntities($resource)
    {
        $json = [
            'resource' => $resource,
        ];
        $body = json_encode($json);
        $endpoint = "/{$this->api_version}/entities";
        $request = new Request('PUT', $endpoint, [], $body);
        $response = $this->send($request);
        return $response;
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
        $endpoint = "/{$this->api_version}/entities/{$uuid}";
        return $this->delete($endpoint);
    }

    /**
     * Purges all entities from the Content Hub.
     *
     * This method should be used carefully as it deletes all the entities for
     * the current subscription from the Content Hub. This creates a backup that
     * can be restored at any time. Any subsequent purges overwrite the existing
     * backup. Be VERY careful when using this endpoint.
     */
    public function purge()
    {
        $endpoint = "/{$this->api_version}/entities/purge";
        $request = new Request('POST', $endpoint, [], '');
        return $this->getResponseJson($request);
    }

    /**
     * Restores the state of entities before the previous purge.
     *
     * Only to be used if a purge has been called previously. This means new
     * entities added after the purge was enacted will be overwritten by the
     * previous state. Be VERY careful when using this endpoint.
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     *   The response.
     */
    public function restore()
    {
        $endpoint = "/{$this->api_version}/entities/restore";
        $request = new Request('POST', $endpoint, [], '');
        return $this->getResponseJson($request);
    }
    /**
     * Reindex a subscription.
     *
     * Schedules a reindex process.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function reindex()
    {
        $endpoint = "/{$this->api_version}/reindex";
        $request = new Request('POST', $endpoint, [], '');
        return $this->getResponseJson($request);
    }

    /**
     * Obtains Customer-Facing-Logs for the subscription.
     *
     * This is forward search request to Elastic Search.
     *
     * @param string $query
     *   An elastic search query.
     * @param array $options
     *   An array with the number of items to show in the list and offset.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function logs($query, $options = [])
    {
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
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function mapping()
    {
        $endpoint = "/{$this->api_version}/_mapping";
        $request = new Request('GET', $endpoint);
        return $this->getResponseJson($request);
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
     * @return array
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function listEntities($options = [])
    {
        $variables = $options + [
            'limit' => 1000,
            'start' => 0,
            'filters' => [],
        ];

        $url = "/{$this->api_version}/entities?limit={$variables['limit']}&start={$variables['start']}";

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
        $request = new Request('GET', $url);
        $items = $this->getResponseJson($request);
        $config = [
            'dataType' => 'ListEntities',
        ];
        $translatedData = $this->adapter->translate($items, $config);
        return $translatedData;
    }

    /**
     * Searches for entities.
     *
     * @param  array                                  $query
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function searchEntity($query)
    {
        $url = "/{$this->api_version}/_search";
        $json = (array) $query;
        $body = json_encode($json);
        $request = new Request('GET', $url, [], $body);
        return $this->getResponseJson($request);
    }

    /**
     * Returns the Client, given the site name.
     *
     * @param string $name
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function getClientByName($name)
    {
        $endpoint = "/{$this->api_version}/settings/clients/{$name}";
        $request = new Request('GET', $endpoint);
        return $this->getResponseJson($request);
    }

    /**
     * Obtains the Settings for the active subscription.
     *
     * @return Settings
     */
    public function getSettings()
    {
        $endpoint = "/{$this->api_version}/settings";
        $request = new Request('GET', $endpoint);
        $data = $this->getResponseJson($request);
        return new Settings($data);
    }

    /**
     * Adds a webhook to the active subscription.
     *
     * @param $webhook_url
     *
     * @return array
     */
    public function addWebhook($webhook_url)
    {
        $json = [
            'url' => $webhook_url
        ];
        $body = json_encode($json);
        $endpoint = "/{$this->api_version}/settings/webhooks";
        $request = new Request('POST', $endpoint, [], $body);
        return $this->getResponseJson($request);
    }

  /**
   * Returns status information for all webhooks.
   * 
   * @return array
   */
    public function getWebhookStatus()
    {
      $endpoint = "/{$this->api_version}/settings/webhooks/status";
      $request = new Request('GET', $endpoint);
      return $this->getResponseJson($request);
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
        $endpoint = "/{$this->api_version}/settings/webhooks/{$uuid}";
        return $this->delete($endpoint);
    }

    /**
     * Regenerates a Shared Secret for the Subscription.
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function regenerateSharedSecret()
    {
        $json = [];
        $body = json_encode($json);
        $endpoint = "/{$this->api_version}/settings/secret";
        $request = new Request('POST', $endpoint, [], $body);
        return $this->getResponseJson($request);
    }

    /**
     * Gets a Json Response from a request.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *   The Request.
     *
     * @return mixed
     */
    protected function getResponseJson(RequestInterface $request)
    {
        $response = $this->send($request);
        $body = (string) $response->getBody();
        return json_decode($body, TRUE);
    }

    /**
     * Fetch snapshots.
     *
     * @return mixed
     *   Response.
     *
     * @throws \Exception
     */
    public function getSnapshots()
    {
      $endpoint = "/v2/snapshots";
      $request = new Request('GET', $endpoint);
      return self::getResponseJson($request);
    }

    /**
     * Create a snapshot.
     *
     * @return mixed
     *   Response.
     *
     * @throws \Exception
     */
    public function createSnapshot()
    {
      $endpoint = "/v2/snapshots";
      $request = new Request('POST', $endpoint, []);
      return self::getResponseJson($request);
    }

    /**
     * Deletes a snapshot.
     *
     * @param string $name
     *   The name of the snapshot.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *   Response.
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function deleteSnapshot($name)
    {
      $endpoint = "/v2/snapshots/$name";
      $request = new Request('DELETE', $endpoint, []);
      return self::getResponseJson($request);
    }

    /**
     * Restore a snapshot.
     *
     * @param string $name
     *   The name of the snapshot.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *   Response.
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function restoreSnapshot($name)
    {
      $endpoint = "/v2/snapshots/$name/restore";
      $request = new Request('PUT', $endpoint, []);
      return self::getResponseJson($request);
    }
}
