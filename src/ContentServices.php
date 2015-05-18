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
     * Sends request to asynchronously create an entity.
     *
     * The entity does not need to be passed to this method, but only the resource URL.
     * An Entity is still accepted for backwards compatibility.
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
        if (is_object($resource)) {
            // Kept only for backwards compatibility.
            $json = (array) $resource;
        }
        else {
            $json = [
                'resource' => $resource,
            ];
        }
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
     * An Entity is still accepted for backwards compatibility.
     * If an entity is passed, it only needs to have the 'resource' parameter set.
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
        if (is_object($resource)) {
            // Kept only for backwards compatibility.
            $json = (array) $resource;
        }
        else {
            $json = [
                'resource' => $resource,
            ];
        }
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
     * Discoverability of the API
     *
     * @param string $endpoint
     *
     * @return Api
     */
    public function definition($endpoint = '')
    {
        $response = $this->options($endpoint);
        return $response->json();
    }

    /**
     * Gets a Plexus User.
     *
     * This has to be updated according to this ticket:
     * https://backlog.acquia.com/browse/COM-646
     *
     * @deprecated
     *
     * @return User
     */
    public function getUser()
    {
        $response = $this->get('user');
        $data = $response->json();
        return new User($data);
    }
}
