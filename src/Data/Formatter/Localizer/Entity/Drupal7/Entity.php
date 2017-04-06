<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7;

use Acquia\ContentHubClient\Data\Formatter\Transformer\Entity\Drupal7 as Transformer;

class Entity
{
    protected $transformer;

    public function __construct()
    {
        $this->transformer = new Transformer();
    }

    public function localizeEntity(&$data)
    {
        $this->transformer->rename($data['attributes'], 'langcode', 'language');
    }

}
