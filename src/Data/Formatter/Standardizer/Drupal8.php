<?php

namespace Acquia\ContentHubClient\Data\Formatter\Standardizer;

use Acquia\ContentHubClient\Attribute;

/**
 * Drupal 8 data standardizer class.
 */
class Drupal8 extends AbstractStandardizer
{
    /**
     * Standardize "Entity".
     *
     * @param mixed $data Data
     */
    protected function standardizeEntity(&$data)
    {
        // Standardizing means having at least UND/EN values in the Entity.
        $defaultLanguageId = $this->config['defaultLanguageId'];

        if (empty($data['attributes'])) {
            return;
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
    }

}
