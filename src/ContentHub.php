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
    const VERSION = '0.6.8';
    const LIBRARYNAME = 'AcquiaContentHubPHPLib';

    private $adapter;

    /**
     * Overrides \GuzzleHttp\Client::__construct()
     *
     * @param string $apiKey
     * @param string $secretKey
     * @param string $origin
     * @param array  $config
     */
    public function __construct($apiKey, $secretKey, $origin, array $config = [])
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
        return $this->get('/ping');
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
        $request = $this->createRequest('POST', '/register', ['json' => $json]);
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
        $request = $this->createRequest('POST', '/entities', ['json' => $json]);
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
        $response = $this->get('entities/' . $uuid);
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
        $request = $this->createRequest('PUT', '/entities/'. $uuid, ['json' => $json]);
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
        $request = $this->createRequest('PUT', '/entities', ['json' => $json]);
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
        return $this->delete('entities/' . $uuid);
    }

    /**
     * Purges all entities from the Content Hub.
     *
     * This method should be used carefully as it deletes all the entities for
     * the current subscription from the Content Hub.
     */
    public function purge()
    {
        $list = $this->listEntities();
        while ($list["total"] != 0) {
            foreach ($list["data"] as $entity) {
                $this->deleteEntity($entity["uuid"]);
            }
            $list = $this->listEntities();
        }
        return $list;
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

        $url = 'entities?limit={limit}&start={start}';

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
        $url = '/_search';
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
        $response = $this->get('/settings/clients/' . $name);
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
        $response = $this->get('settings');
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
        $request = $this->createRequest('POST', '/settings/webhooks', ['json' => $json]);
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
        return $this->delete('/settings/webhooks/' . $uuid);
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
        $request = $this->createRequest('POST', '/settings/secret', ['json' => []]);
        $response = $this->send($request);
        return $response->json();
    }
}
