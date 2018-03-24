<?php

namespace Acquia\ContentHubClient\test\hmacv1;

use Acquia\ContentHubClient\hmacv1\KeyLoaderInterface;

class DummyKeyLoader implements KeyLoaderInterface
{
    protected $keys = array(
        '1' => 'secret-key',
    );

    /**
     * {@inheritDoc}
     */
    public function load($id)
    {
        if (!isset($this->keys[$id])) {
            return false;
        }

        return new DummyKey($id, $this->keys[$id]);
    }
}
