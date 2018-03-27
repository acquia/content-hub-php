<?php

namespace Acquia\ContentHubClient\hmacv1;

interface KeyInterface
{
    /**
     * Returns the key's identifier.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns the key's secret.
     *
     * @return string
     */
    public function getSecret();
}
