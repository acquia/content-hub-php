<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7;

class ArrayBoolean extends Attribute
{
    public function localizeEntity(&$data)
    {
        foreach ($data['value'] as $langcode => $valueList) {
            foreach ($valueList as $key => $value) {
                $data['value'][$langcode][$key] = $value === FALSE ? null : $value;
            }
        }
    }

}
