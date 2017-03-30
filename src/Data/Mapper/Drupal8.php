<?php

namespace Acquia\ContentHubClient\Data\Mapper;

use Acquia\ContentHubClient\Attribute;

class Drupal8 extends Mappable
{
    protected function standardizeEntity($data)
    {
        // Standardizing means having at least UND/EN values in the Entity.
        $defaultLanguageId = $this->config['defaultLanguageId'];

        if (empty($data['attributes'])) {
            return $data;
        }

        foreach ($data['attributes'] as $attributeName => $attributeValue) {
            if (isset($attributeValue['value'][$defaultLanguageId]) && !isset($attributeValue['value'][Attribute::LANGUAGE_DEFAULT])) {
                $attributeValue = $attributeName === 'langcode' ? Attribute::LANGUAGE_DEFAULT : $attributeValue['value'][$defaultLanguageId];
                $data['attributes'][$attributeName]['value'][Attribute::LANGUAGE_DEFAULT] = $attributeValue;
            }
            if (isset($attributeValue['value'][Attribute::LANGUAGE_DEFAULT]) && !isset($attributeValue['value'][$defaultLanguageId])) {
                $attributeValue = $attributeName === 'language' ? $defaultLanguageId : $attributeValue['value'][Attribute::LANGUAGE_DEFAULT];
                $data['attributes'][$attributeName]['value'][$defaultLanguageId] = $attributeValue;
            }
        }

        return $data;
    }

    /**
     * Localizes the listEntities data.
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
