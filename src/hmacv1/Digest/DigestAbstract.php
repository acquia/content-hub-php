<?php

namespace Acquia\ContentHubClient\hmacv1\Digest;

use Acquia\ContentHubClient\hmacv1\RequestSignerInterface;
use Acquia\ContentHubClient\hmacv1\Request\RequestInterface;

abstract class DigestAbstract implements DigestInterface
{
    /**
     * @var string
     */
    protected $algorithm;

    /**
     * @param string $algorithm
     */
    public function __construct($algorithm = 'sha1')
    {
        $this->algorithm = $algorithm;
    }

    /**
     * @param string $algorithm
     *
     * @return \Acquia\ContentHubClient\hmacv1\Digest\DigestAbstract
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $algorithm;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    /**
     * {@inheritDoc}
     */
    public function get(RequestSignerInterface $requestSigner, RequestInterface $request, $secretKey)
    {
        $message = $this->getMessage($requestSigner, $request);
        $digest = hash_hmac($this->algorithm, $message, $secretKey, true);
        return base64_encode($digest);
    }

    /**
     * Returns the message being signed.
     *
     * @param \Acquia\ContentHubClient\hmacv1\RequestSignerInterface $requestSigner
     * @param \Acquia\ContentHubClient\hmacv1\Request\RequestInterface $request
     *
     * @return string
     */
    abstract protected function getMessage(RequestSignerInterface $requestSigner, RequestInterface $request);
}
