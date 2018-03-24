<?php

namespace Drupal\acquia_contenthub\hmacv1\Test;

use Drupal\acquia_contenthub\hmacv1\KeyLoaderInterface;

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
