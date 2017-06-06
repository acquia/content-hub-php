<?php

namespace Acquia\ContentHubClient;

use Acquia\Hmac\Digest as Digest;
use GuzzleHttp\Client;
use Acquia\Hmac\RequestSigner;
use Acquia\Hmac\Guzzle5\HmacAuthPlugin;
use Acquia\ContentHubClient\Data\Adapter;

class ContentHub extends Client
{
    // Override VERSION inherited from GuzzleHttp::ClientInterface
    const VERSION = '0.6.5';
    const LIBRARYNAME = 'AcquiaContentHubPHPLib';

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
     * @param string $apiKey
     * @param string $secretKey
     * @param string $origin
     * @param array  $config
     * @param string  $api_version
     */
    public function __construct($apiKey, $secretKey, $origin, array $config = [], $api_version = 'v1')
    {
        if (!isset($config['defaults'])) {
            $config['defaults'] = [];
        }

        if (!isset($config['defaults']['headers'])) {
            $config['defaults']['headers'] = [];
        }

        $user_agent_string = $this::LIBRARYNAME . '/' . $this::VERSION . ' ' . static::getDefaultUserAgent();
        if (isset($config['client-user-agent'])) {
            $user_agent_string = $config['client-user-agent'] . ' ' . $user_agent_string;
        }

        // Setting up the headers.
        $config['defaults']['headers'] += [
            'Content-Type' => 'application/json',
            'X-Acquia-Plexus-Client-Id' => $origin,
            'User-Agent' => $user_agent_string,
        ];

        $adapterConfig = isset($config['adapterConfig']) ? $config['adapterConfig'] : [];
        unset($config['adapterConfig']);

        parent::__construct($config);

        // Define the API Version.
        $this->api_version = $api_version;

        // Set the Adapter.
        $this->adapter = new Adapter($adapterConfig);

        // Add the authentication plugin
        // @see https://github.com/acquia/http-hmac-spec
        $requestSigner = new RequestSigner(new Digest\Version1('sha256'));
        $plugin = new HmacAuthPlugin($requestSigner, $apiKey, $secretKey);
        $this->getEmitter()->attach($plugin);
    }

    /**
     * Pings the service to ensure that it is available.
     *
     * @return \GuzzleHttp\Message\Response
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
        $response = $this->options($endpoint);
        return $response->json();
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
        $endpoint = "/{$this->api_version}/register";
        $request = $this->createRequest('POST', $endpoint, ['json' => $json]);
        $response = $this->send($request);
        return $response->json();
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
     * @return \GuzzleHttp\Message\Response
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
     * @return \GuzzleHttp\Message\Response
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function createEntities($resource)
    {
        $json = [
            'resource' => $resource,
        ];
        $endpoint = "/{$this->api_version}/entities";
        $request = $this->createRequest('POST', $endpoint, ['json' => $json]);
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
        $response = $this->get($endpoint);
        $data = $response->json();
        $config = [
            'dataType' => 'Entity',
        ];
        $translatedData = $this->adapter->translate($data['data']['data'], $config);
        return new Entity($translatedData);
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
     * @return \GuzzleHttp\Message\Response
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function updateEntity($resource, $uuid)
    {
        $json = [
            'resource' => $resource,
        ];
        $endpoint = "/{$this->api_version}/entities/{$uuid}";
        $request = $this->createRequest('PUT', $endpoint, ['json' => $json]);
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
     * @return \GuzzleHttp\Message\Response
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function updateEntities($resource)
    {
        $json = [
            'resource' => $resource,
        ];
        $endpoint = "/{$this->api_version}/entities";
        $request = $this->createRequest('PUT', $endpoint, ['json' => $json]);
        $response = $this->send($request);
        return $response;
    }

    /**
     * Deletes an entity by UUID.
     *
     * @param  string                                 $uuid
     *
     * @return \GuzzleHttp\Message\Response
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
     *
     * @return array
     *   The response array.
     */
    public function purge()
    {
        $endpoint = "/{$this->api_version}/entities/purge";
        $request = $this->createRequest('POST', $endpoint, ['json' => '']);
        $response = $this->send($request);
        return $response->json();
    }

    /**
     * Restores the state of entities before the previous purge.
     *
     * Only to be used if a purge has been called previously. This means new
     * entities added after the purge was enacted will be overwritten by the
     * previous state. Be VERY careful when using this endpoint.
     *
     * @return array
     *   The response array.
     */
    public function restore()
    {
        $endpoint = "/{$this->api_version}/entities/restore";
        $request = $this->createRequest('POST', $endpoint, ['json' => '']);
        $response = $this->send($request);
        return $response->json();
    }

    /**
     * Reindex a subscription.
     *
     * Schedules a reindex process.
     *
     * @return array
     *   The response array.
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function reindex()
    {
        $endpoint = "/{$this->api_version}/reindex";
        $request = $this->createRequest('POST', $endpoint, ['json' => '']);
        $response = $this->send($request);
        return $response->json();
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
     * @return array
     *   The history logs array.
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function logs($query, $options = [])
    {
        // If no specific ElasticSearch query is given, use a simple query.
        $options = $options + [
            'size' => 20,
            'from' => 0
        ];
        $json = empty($query) ? '{"query": {"match_all": {}}}' : $query;
        $query = json_decode($json, TRUE);

        // Execute request.
        $endpoint = "/{$this->api_version}/history?size={$options['size']}&from={$options['from']}";
        $request = $this->createRequest('POST', $endpoint, ['json' => $query]);
        $response = $this->send($request);
        return $response->json();
    }

    /**
     * Retrieves active ElasticSearch mapping of entities.
     *
     * @return array
     *   The fields mapping array.
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function mapping()
    {
        $endpoint = "/{$this->api_version}/_mapping";
        $response = $this->get($endpoint);
        return $response->json();
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

        $url = "/{$this->api_version}/entities?limit={limit}&start={start}";

        $url .= isset($variables['type']) ? '&type={type}' :'';
        $url .= isset($variables['origin']) ? '&origin={origin}' :'';
        $url .= isset($variables['language']) ? '&language={language}' :'';
        $url .= isset($variables['fields']) ? '&fields={fields}' :'';
        foreach ($variables['filters'] as $name => $value) {
            $filter = 'filter_' . $name;
            $variables[$filter] = $value;
            $url .= isset($value) ? sprintf('&filter:%s={%s}', $name, $filter) : '';
        }
        unset($variables['filters']);

        // Now make the request.
        $response = $this->get(array($url, $variables));
        $items = $response->json();

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
        $request = $this->createRequest('GET', $url, ['json' => (array) $query]);
        $response = $this->send($request);
        return $response->json();
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
        $response = $this->get($endpoint);
        $data = $response->json();
        return $data;
    }

    /**
     * Obtains the Settings for the active subscription.
     *
     * @return Settings
     */
    public function getSettings()
    {
        $endpoint = "/{$this->api_version}/settings";
        $response = $this->get($endpoint);
        $data = $response->json();
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
        $endpoint = "/{$this->api_version}/settings/webhooks";
        $request = $this->createRequest('POST', $endpoint, ['json' => $json]);
        $response = $this->send($request);
        return $response->json();
    }

    /**
     * Deletes a webhook from the active subscription.
     *
     * @param $uuid
     *   The UUID of the webhook to delete
     *
     * @return \GuzzleHttp\Message\Response
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
        $endpoint = "/{$this->api_version}/settings/secret";
        $request = $this->createRequest('POST', $endpoint, ['json' => []]);
        $response = $this->send($request);
        return $response->json();
    }
}
