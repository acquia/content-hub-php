<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7;

/**
 * Boolean attribute data localizer class.
 */
class Boolean extends Attribute
{
    /**
     * Localize "entity".
     *
     * @param mixed $data Data
     */
    public function localizeEntity(&$data)
    {
        if (!isset($data['value'])) {
            return;
        }

        foreach ($data['value'] as $langcode => $value) {
            $data['value'][$langcode] = $value === FALSE ? NULL : $value;
        }
    }

}
