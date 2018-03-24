<?php

namespace Drupal\acquia_contenthub\hmacv1\Test;

use Drupal\acquia_contenthub\hmacv1\KeyInterface;

class DummyKey implements KeyInterface
{
    protected $id;

    protected $secret;

    public function __construct($id, $secret)
    {
        $this->id = $id;
        $this->secret = $secret;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSecret()
    {
        return $this->secret;
    }
}
