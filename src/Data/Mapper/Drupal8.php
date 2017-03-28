<?php

namespace Acquia\ContentHubClient\Data\Mapper;

use Acquia\ContentHubClient\Attribute;

class Drupal8 extends Mappable
{
    protected function standardizeEntity($data, $config)
    {
        // Standarizing means having at least UND/EN values in the Entity.
        $language_standard = 'en';

        if (empty($data['attributes'])) {
            return $data;
        }

        foreach ($data['attributes'] as $attribute_name => $attribute_value) {
            if (isset($attribute_value['value'][$language_standard]) && !isset($attribute_value['value'][Attribute::LANGUAGE_DEFAULT])) {
                $data['attributes'][$attribute_name]['value'][Attribute::LANGUAGE_DEFAULT] = $attribute_value['value'][$language_standard];
            }
            if (isset($attribute_value['value'][Attribute::LANGUAGE_DEFAULT]) && !isset($attribute_value['value'][$language_standard])) {
                $data['attributes'][$attribute_name]['value'][$language_standard] = $attribute_value['value'][Attribute::LANGUAGE_DEFAULT];
            }
        }

        return $data;
    }

    protected function standardizeListEntities($data, $config)
    {
        return $data;
    }
}
