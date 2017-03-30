<?php

namespace Acquia\ContentHubClient\Data\Formatter\Standardizer;

use Acquia\ContentHubClient\Attribute;

class Mixed extends Standardizable
{
    protected function standardizeListEntities($data)
    {
        // Standarizing means having at least UND/EN values in the Entity.
        $language_standard = $this->config['defaultLanguageId'];

        if (empty($data['data'])) {
            return $data;
        }

        foreach($data['data'] as $key => $item) {
            if (empty($item['attributes'])) {
                continue;
            }

            foreach ($item['attributes'] as $attributeName => $attributeValue) {
                if (isset($attributeValue[$language_standard]) && !isset($attributeValue[Attribute::LANGUAGE_DEFAULT])) {
                    $data['data'][$key]['attributes'][$attributeName][Attribute::LANGUAGE_DEFAULT] = $attributeValue[$language_standard];
                }
                if (isset($attributeValue[Attribute::LANGUAGE_DEFAULT]) && !isset($attributeValue[$language_standard])) {
                    $data['data'][$key]['attributes'][$attributeName][$language_standard] = $attributeValue[Attribute::LANGUAGE_DEFAULT];

                }
            }
        }

        return $data;
    }

}
