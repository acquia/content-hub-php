<?php

namespace Acquia\ContentServicesClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Crell\ApiProblem\ApiProblem;

class ContentServices extends Client
{
    /**
     * Overrides \GuzzleHttp\Client::_construct()
     */
    public function _construct(array $config = [])
    {
        parent::__construct($config);
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
     * @param string $uuid
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
     * @param RequestException $ex
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
