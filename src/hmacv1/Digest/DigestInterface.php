<?php

namespace Acquia\ContentHubClient\hmacv1\Digest;

use Acquia\ContentHubClient\hmacv1\Request\RequestInterface;
use Acquia\ContentHubClient\hmacv1\RequestSignerInterface;

interface DigestInterface
{
    /**
     * Returns the signature.
     *
     * @param \Acquia\ContentHubClient\hmacv1\RequestSignerInterface $requestSigner
     * @param \Acquia\ContentHubClient\hmacv1\Request\RequestInterface $request
     * @param string $secretKey
     *
     * @return string
     */
    public function get(RequestSignerInterface $requestSigner, RequestInterface $request, $secretKey);
}
