<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7;

class Boolean
{
    public function localizeEntity(&$data)
    {
        foreach ($data['value'] as $langcode => $value) {
            $data['value'][$langcode] = $value === FALSE ? null : $value;
        }
    }

}
