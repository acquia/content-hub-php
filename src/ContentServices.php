<?php

namespace Acquia\ContentServicesClient;

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
        $config['defaults'] += array(
            'headers' => ['Content-Type' => 'application/json'],
        );

        parent::__construct($config);
    }

    /**
     * @param array $config
     * @return static
     */
    public static function factory($config = array())
    {
        $apikey = $config['defaults']['auth']['username'];
        $secretkey = $config['defaults']['auth']['password'];

        $requestSigner = new RequestSigner();
        $plugin = new HmacAuthPlugin($requestSigner, $apikey, $secretkey);

        $client = new Client($config);
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
        } catch (Exception $ex) {
            $this->throwException($ex);
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
