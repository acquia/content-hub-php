<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7;

class Boolean extends Attribute
{
    public function localizeEntity(&$data)
    {
        foreach ($data['value'] as $langcode => $value) {
            $data['value'][$langcode] = $value === FALSE ? NULL : $value;
        }
    }

}
