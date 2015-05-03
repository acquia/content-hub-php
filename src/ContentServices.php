<?php

namespace Acquia\ContentServicesClient;

use Acquia\Hmac\Digest as Digest;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Crell\ApiProblem\ApiProblem;
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
     * @param array $config
     * @return static
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
     *
     * @param \Acquia\ContentServicesClient\Entity $entity
     *
     * @return ContentServicesException
     */
    public function createEntity(Entity $entity)
    {
        try {
            $request = $this->createRequest('POST', '/entities', ['json' => (array) $entity]);
            $response = $this->send($request);
            $entity->exchangeArray($response->json());

            return $response;
        } catch (RequestException $ex) {
            $this->throwException($ex);
        }
    }

    /**
     * @param string $uuid
     *
     * @return ContentServicesException
     */
    public function readEntity($uuid)
    {
        try {
            $response = $this->get('entities/'.$uuid);
            return new Entity($response->json());
        } catch (\GuzzleHttp\Exception\ClientException $ex) {
            return new Entity($ex->getResponse()->json());
        } catch (\GuzzleHttp\Exception\ServerErrorResponseException $ex) {
            return new Entity($ex->getResponse()->json());
        } catch (\GuzzleHttp\Exception\BadResponseException $ex) {
            return new Entity($ex->getResponse()->json());
        }
    }

    /**
     * @param \Acquia\ContentServicesClient\Entity $entity
     * @param string                               $uuid
     *
     * @return ContentServicesException
     */
    public function updateEntity(Entity $entity, $uuid)
    {
        try {
            $request = $this->createRequest('PUT', '/entities/'.$uuid, ['json' => (array) $entity]);
            $response = $this->send($request);
            $entity->exchangeArray($response->json());

            return $response;
        } catch (RequestException $ex) {
            $this->throwException($ex);
        }
    }

    /**
     * @param string $uuid
     *
     * @return ContentServicesException
     */
    public function deleteEntity($uuid)
    {
        try {
            return $this->delete('entities/'.$uuid);
        } catch (RequestException $ex) {
            $this->throwException($ex);
        }
    }

    /**
     * @param $search_term
     * @param $index
     * @return mixed
     */
    public function searchEntity($index, $query)
    {
        $url = '/elastic/'.$index.'/_search';

        try {
            $request = $this->createRequest('POST', $url, ['json' => (array) $query]);
            $response = $this->send($request);
            $json = $response->json();
            return $json;
        } catch (RequestException $ex) {
            $this->throwException($ex);
        }
    }

    /**
     * @param  RequestException         $ex
     * @throws ContentServicesException
     */
    public function throwException(RequestException $ex)
    {
        if ($ex->hasResponse()) {
            $response = $ex->getResponse();
            $problem = ApiProblem::fromJson($response->getBody());
            $problem->setStatus($response->getStatusCode());
            throw new ContentServicesException($problem, $ex);
        } else {
            $problem = new ApiProblem('Unknown error occured');
            throw new ContentServicesException($problem, $ex);
        }
    }
}
