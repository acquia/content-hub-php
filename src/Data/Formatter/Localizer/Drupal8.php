<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer;

/**
 * Drupal 8 data localizer class.
 */
class Drupal8 extends AbstractLocalizer
{
    /**
     * Localize "listEntities".
     *
     * @param mixed $data
     *
     * @return mixed
     */
    protected function localizeListEntities($data)
    {
        return $data;
    }

}
