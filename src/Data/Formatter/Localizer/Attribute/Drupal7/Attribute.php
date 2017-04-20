<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7;

use Acquia\ContentHubClient\Data\Formatter\Transformer\General as Transformer;

/**
 * Attribute data localizer class.
 */
class Attribute
{
    /**
     * Transformer.
     *
     * @var \Acquia\ContentHubClient\Data\Formatter\Transformer\General
     */
    protected $transformer;

    /**
     * Attribute constructor.
     */
    public function __construct()
    {
        $this->transformer = new Transformer();
    }

}
