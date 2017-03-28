<?php

namespace Acquia\ContentHubClient\Data\Mapper;

use Acquia\ContentHubClient\Attribute;

class Drupal8 extends Mappable
{
    protected function standardizeEntity($data, $config)
    {
        // Standarizing means having at least UND/EN values in the Entity.
        $languageStandard = 'en';

        if (empty($data['attributes'])) {
            return $data;
        }

        foreach ($data['attributes'] as $attributeName => $attributeValue) {
            if (isset($attributeValue['value'][$languageStandard]) && !isset($attributeValue['value'][Attribute::LANGUAGE_DEFAULT])) {
                $attributeValue = $attributeName === 'langcode' ? Attribute::LANGUAGE_DEFAULT : $attributeValue['value'][$languageStandard];
                $data['attributes'][$attributeName]['value'][Attribute::LANGUAGE_DEFAULT] = $attributeValue;
            }
            if (isset($attributeValue['value'][Attribute::LANGUAGE_DEFAULT]) && !isset($attributeValue['value'][$languageStandard])) {
                $attributeValue = $attributeName === 'language' ? $languageStandard : $attributeValue['value'][Attribute::LANGUAGE_DEFAULT];
                $data['attributes'][$attributeName]['value'][$languageStandard] = $attributeValue;
            }
        }

        return $data;
    }

    protected function standardizeListEntities($data, $config)
    {
        return $data;
    }

}
