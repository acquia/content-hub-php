<?php

namespace Acquia\ContentHubClient\hmacv1;

use Acquia\ContentHubClient\hmacv1\Request\RequestInterface;

interface RequestSignerInterface
{
    /**
     * Generates a signature for the request given the secret key and algorithm.
     *
     * @param \Acquia\ContentHubClient\hmacv1\Request\RequestInterface $request
     * @param string $secretKey
     *
     * @return string
     */
    public function signRequest(RequestInterface $request, $secretKey);

    /**
     * Returns the value of the "Authorization" header.
     *
     * @param \Acquia\ContentHubClient\hmacv1\Request\RequestInterface $request
     * @param string $id
     * @param string $secretKey
     *
     * @return string
     */
    public function getAuthorization(RequestInterface $request, $id, $secretKey);

    /**
     * Gets the signature passed through the HTTP request.
     *
     * @param \Acquia\ContentHubClient\hmacv1\Request\RequestInterface $request
     *
     * @return \Acquia\ContentHubClient\hmacv1\SignatureInterface
     */
    public function getSignature(RequestInterface $request);

    /**
     * Returns the content type passed through the request.
     *
     * @param \Acquia\ContentHubClient\hmacv1\Request\RequestInterface $request
     *
     * @return string
     *
     * @throws \Acquia\ContentHubClient\hmacv1\Exception\MalformedRequestException
     */
    public function getContentType(RequestInterface $request);

    /**
     * Returns timestamp passed through the request.
     *
     * @param \Acquia\ContentHubClient\hmacv1\Request\RequestInterface $request
     *
     * @return string
     *
     * @throws \Acquia\ContentHubClient\hmacv1\Exception\MalformedRequestException
     */
    public function getTimestamp(RequestInterface $request);

    /**
     * Returns an associative array of custom headers.
     *
     * @param \Acquia\ContentHubClient\hmacv1\Request\RequestInterface $request
     *
     * @return string
     *
     * @throws \Acquia\ContentHubClient\hmacv1\Exception\MalformedRequestException
     */
    public function getCustomHeaders(RequestInterface $request);
}
