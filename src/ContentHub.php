<?php

namespace Acquia\ContentHubClient;

use Acquia\Hmac\Digest as Digest;
use Acquia\Hmac\Guzzle\HmacAuthMiddleware;
use Acquia\Hmac\RequestSigner;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;

class ContentHub extends Client
{
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
        // "base_url" parameter changed to "base_uri" in Guzzle6, so the following line
        // is there to make sure it does not disrupt previous configuration.
        if (!isset($config['base_uri']) && isset($config['base_url'])) {
            $config['base_uri'] = $config['base_url'];
        }

        // Setting up the headers.
        $config['headers']['Content-Type'] = 'application/json';
        $config['headers']['X-Acquia-Plexus-Client-Id'] = $origin;

        // Add the authentication handler
        // @see https://github.com/acquia/http-hmac-spec
        $requestSigner = new RequestSigner(new Digest\Version1('sha256'));
        $middleware = new HmacAuthMiddleware($requestSigner, $apiKey, $secretKey);

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
        $request = new Request('POST', '/register', [], $body);
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
        $request = new Request('POST', '/entities', [], $body);
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
        $request = new Request('GET', '/entities/' . $uuid);
        $data = $this->getResponseJson($request);
        return new Entity($data['data']['data']);
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
        $request = new Request('PUT', '/entities/' . $uuid, [], $body);
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
        $request = new Request('PUT', '/entities', [], $body);
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
        return $this->delete('/entities/' . $uuid);
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
        $request = new Request('GET', $url);
        return $this->getResponseJson($request);
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
        $request = new Request('GET', '/settings/clients/' . $name);
        return $this->getResponseJson($request);
    }

    /**
     * Obtains the Settings for the active subscription.
     *
     * @return Settings
     */
    public function getSettings()
    {
        $request = new Request('GET', '/settings');
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
        $request = new Request('POST', '/settings/webhooks', [], $body);
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
    
    /**
     * Reindex
     *
     * Schedules a reindex process.
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function reindex()
    {
        $json = [];
        $body = json_encode($json);
        $request = new Request('POST', '/reindex', [], $body);
        $response = $this->send($request);
        return $response;
    }

    protected function getResponseJson(RequestInterface $request)
    {
        $response = $this->send($request);
        $body = (string) $response->getBody();
        return json_decode($body, TRUE);
    }
}
