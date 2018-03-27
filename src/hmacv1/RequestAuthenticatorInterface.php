<?php

namespace Acquia\ContentHubClient\hmacv1;

interface RequestAuthenticatorInterface
{
    /**
     * Authenticates the passed request.
     *
     * @param \Acquia\ContentHubClient\hmacv1\Request\RequestInterface $request
     * @param \Acquia\ContentHubClient\hmacv1\KeyLoaderInterface $keyLoader
     *
     * @return \Acquia\ContentHubClient\hmacv1\KeyInterface
     *
     * @throws \Acquia\ContentHubClient\hmacv1\Exception\InvalidRequestException
     */
    public function authenticate(Request\RequestInterface $request, KeyLoaderInterface $keyLoader);
}
