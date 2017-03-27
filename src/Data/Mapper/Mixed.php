<?php

namespace Acquia\ContentHubClient\Data\Mapper;

use Acquia\ContentHubClient\Attribute;

class Mixed extends Mappable
{
    protected function standardizeListEntities($data)
    {
        // Standarizing means having at least UND/EN values in the Entity.
        $language_standard = 'en';

        if (empty($data['data'])) {
            return $data;
        }

        foreach($data['data'] as $key => $item) {
            foreach ($item['attributes'] as $attribute_name => $attribute_value) {
                if (isset($attribute_value[$language_standard])) {
                    $attribute_value[Attribute::LANGUAGE_DEFAULT] = $attribute_value[$language_standard];
                }
                elseif (isset($attribute_value[Attribute::LANGUAGE_DEFAULT])) {
                    $attribute_value[$language_standard] = $attribute_value[Attribute::LANGUAGE_DEFAULT];
                }
                $item['attributes'][$attribute_name] = $attribute_value;
            }
            $data['data'][$key] = $item;
        }
        return $data;
    }



}
