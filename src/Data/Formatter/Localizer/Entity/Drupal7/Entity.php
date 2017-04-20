<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Entity\Drupal7;

use Acquia\ContentHubClient\Data\Formatter\Transformer\Entity\Drupal7 as Transformer;

/**
 * Entity data localizer class.
 */
class Entity
{
    /**
     * Transformer.
     *
     * @var \Acquia\ContentHubClient\Data\Formatter\Transformer\Entity\Drupal7
     */
    protected $transformer;

    /**
     * Entity constructor.
     */
    public function __construct()
    {
        $this->transformer = new Transformer();
    }

    /**
     * Localize "entity".
     *
     * @param mixed $data Data
     */
    public function localizeEntity(&$data)
    {
        if (!isset($data['attributes'])) {
            return;
        }

        $this->transformer->rename($data['attributes'], 'langcode', 'language');
    }

}
