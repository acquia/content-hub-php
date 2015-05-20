<?php

namespace Acquia\ContentServicesClient;

use Acquia\Hmac\Digest as Digest;
use GuzzleHttp\Client;
use Acquia\Hmac\RequestSigner;
use Acquia\Hmac\Guzzle5\HmacAuthPlugin;

class ContentServices extends Client
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
        if (!isset($config['defaults'])) {
            $config['defaults'] = [];
        }

        if (!isset($config['defaults']['headers'])) {
            $config['defaults']['headers'] = [];
        }

        // Setting up the headers.
        $config['defaults']['headers'] += [
            'Content-Type' => 'application/json',
            'X-Acquia-Plexus-Client-Id' => $origin,
        ];

        parent::__construct($config);

        // Add the authentication plugin
        // @see https://github.com/acquia/http-hmac-spec
        $requestSigner = new RequestSigner(new Digest\Version1('sha256'));
        $plugin = new HmacAuthPlugin($requestSigner, $apiKey, $secretKey);
        $this->getEmitter()->attach($plugin);
    }

    /**
     * @param  array                                         $config
     *
     * @return \Acquia\ContentServicesClient\ContentServices
     *
     * @deprecated 0.2.0
     */
    public static function factory($config = array())
    {
        $apikey = $config['defaults']['auth'][0];
        $secretkey = $config['defaults']['auth'][1];
        $origin = $config['origin'];

        return new static($apikey, $secretkey, $origin, $config);
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
     * @param  string $resource
     *   This string should contain the URL where Plexus can read the entity's CDF.
     *
     * @return \GuzzleHttp\Message\Response
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function createEntity($resource)
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
     * @return \Acquia\ContentServicesClient\Entity
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function readEntity($uuid)
    {
        $response = $this->get('entities/' . $uuid);
        $data = $response->json();
        return new Entity($data['data']['Data']);
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
     * Searches for entities.
     *
     * @param  string                                 $index
     * @param  array                                  $query
     *
     * @return array
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function searchEntity($index, $query)
    {
        $url = '/elastic/' . $index . '/_search';
        $request = $this->createRequest('POST', $url, ['json' => (array) $query]);
        $response = $this->send($request);
        return $response->json();
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
}
