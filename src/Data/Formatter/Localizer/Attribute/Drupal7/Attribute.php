<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7;

use Acquia\ContentHubClient\Data\Formatter\Transformer\General as Transformer;

class Attribute
{
    protected $transformer;

    public function __construct()
    {
        $this->transformer = new Transformer();
    }

}
