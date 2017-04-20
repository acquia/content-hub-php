<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7;

/**
 * ArrayBoolean attribute data localizer class.
 */
class ArrayBoolean extends Attribute
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

        foreach ($data['value'] as $langcode => $valueList) {
            foreach ($valueList as $key => $value) {
                $data['value'][$langcode][$key] = $value === FALSE ? NULL : $value;
            }
        }
    }

}
