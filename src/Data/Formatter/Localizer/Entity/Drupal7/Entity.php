<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7;

use Acquia\ContentHubClient\Data\Formatter\Massager\Drupal7 as Massager;

class Entity
{
    protected $massager;

    public function __construct()
    {
        $this->massager = new Massager();
    }

    public function localizeEntity(&$data)
    {
        $this->massager->rename($data['attributes'], 'langcode', 'language');
    }

}
