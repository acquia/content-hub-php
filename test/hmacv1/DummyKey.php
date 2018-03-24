<?php

namespace Acquia\ContentHubClient\test\hmacv1;

use Acquia\ContentHubClient\hmacv1\KeyInterface;

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
