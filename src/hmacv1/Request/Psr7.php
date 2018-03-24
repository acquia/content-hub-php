<?php

namespace Acquia\ContentHubClient\hmacv1\Request;

use Psr\Http\Message\RequestInterface as Psr7RequestInterface;

class Psr7 implements RequestInterface
{
    /**
     * @var \Psr\Http\Message\RequestInterface
     */
    protected $request;

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     */
    public function __construct(Psr7RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritDoc}
     */
    public function hasHeader($header)
    {
        return $this->request->hasHeader($header);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader($header)
    {
        return $this->request->getHeaderLine($header);
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod()
    {
        return $this->request->getMethod();
    }

    /**
     * {@inheritDoc}
     */
    public function getBody()
    {
        return $this->request->getBody();
    }

    /**
     * {@inheritDoc}
     */
    public function getResource()
    {
        return $this->request->getRequestTarget();
    }
}
