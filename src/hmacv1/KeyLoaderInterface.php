<?php

namespace Acquia\ContentHubClient\hmacv1;

interface KeyLoaderInterface
{
    /**
     * @param string $id
     *
     * @return \Acquia\ContentHubClient\hmacv1\KeyInterface|false
     */
    public function load($id);
}
