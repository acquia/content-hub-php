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
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['defaults'])) {
            $config['defaults'] = [];
        }

        // Setting up the headers.
        $headers = ['Content-Type' => 'application/json'];
        if (isset($config['origin'])) {
            $headers['X-Acquia-Plexus-Client-Id'] = $config['origin'];
        }

        // Setting up the defaults.
        $config['defaults'] += array(
            'headers' => $headers,
        );

        parent::__construct($config);
    }

    /**
     * @param  array                                         $config
     *
     * @return \Acquia\ContentServicesClient\ContentServices
     */
    public static function factory($config = array())
    {
        $apikey = $config['defaults']['auth'][0];
        $secretkey = $config['defaults']['auth'][1];

        // Using sha256 algorithm by default.
        $digest = new Digest\Version1('sha256');

        $requestSigner = new RequestSigner($digest);
        $plugin = new HmacAuthPlugin($requestSigner, $apikey, $secretkey);

        $client = new static($config);
        $client->getEmitter()->attach($plugin);
        return $client;
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
     * Sends entity creation request to Plexus.
     *
     * @param  \Acquia\ContentServicesClient\Entity   $entity
     *
     * @return \GuzzleHttp\Message\Response
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function createEntity(Entity $entity)
    {
        $request = $this->createRequest('POST', '/entities', ['json' => (array) $entity]);
        $response = $this->send($request);
        $entity->exchangeArray($response->json());
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
        return new Entity($response->json());
    }

    /**
     * Updates the passed entity.
     *
     * @param  \Acquia\ContentServicesClient\Entity   $entity
     * @param  string                                 $uuid
     *
     * @return \GuzzleHttp\Message\Response
     *
     * @throws \GuzzleHttp\Exception\RequestException
     */
    public function updateEntity(Entity $entity, $uuid)
    {
        $request = $this->createRequest('PUT', '/entities/'.$uuid, ['json' => (array) $entity]);
        $response = $this->send($request);
        $entity->exchangeArray($response->json());
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
     * Search for entities.
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
}
