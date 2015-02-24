<?php

namespace Acquia\ContentServicesClient;

use GuzzleHttp\Exception\RequestException;
use Crell\ApiProblem\ApiProblem;

class ContentServicesException extends \RuntimeException
{
    /**
     * @var ApiProblem
     */
    protected $problem;

    /**
     * @param ApiProblem      $problem
     * @param RequestException $previous
     */
    public function __construct(ApiProblem $problem, RequestException $previous)
    {
        $this->problem = $problem;
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
    }

    /**
     * @return ApiProblem
     */
    public function getProblem()
    {
        return $this->problem;
    }
}
