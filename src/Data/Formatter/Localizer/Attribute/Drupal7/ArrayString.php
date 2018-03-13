<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7;

/**
 * ArrayString attribute data localizer class.
 */
class ArrayString extends Attribute
{
    /**
     * Config.
     *
     * @var array
     */
    private $config = [
        'filters_mapping' => [
            'restricted_html' => 'filtered_html',
            'basic_html' => 'filtered_html',
            'full_html' => 'full_html',
            'rich_text' => 'full_html',
        ],
    ];

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
            if (is_array($valueList)) {
                foreach ($valueList as $key => $value) {
                    $fieldValue = json_decode($value, TRUE);
                    if (!is_array($fieldValue)) {
                        $valueList[$key] = $value;
                        continue;
                    }

                    // Localize Link fields - Address.
                    // @TODO: Fix this for Address Type.
                    if (isset($fieldValue['country_code'])) {
                        $this->transformer->rename($fieldValue, 'country_code', 'country');
                        $this->transformer->rename($fieldValue, 'address_line1', 'thoroughfare');
                        $this->transformer->rename($fieldValue, 'address_line2', 'premise');
                        $this->transformer->rename($fieldValue, 'organization', 'organisation_name');
                        $this->transformer->rename($fieldValue, 'given_name', 'first_name');
                        $this->transformer->rename($fieldValue, 'family_name', 'last_name');
                        $valueList[$key] = json_encode($fieldValue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                        continue;
                    }

                    // Localize Link fields - Hack.
                    // @TODO: Fix this for Links Type.
                    if (isset($fieldValue['uri'])) {
                        // Strip out the 'internal:/' prefix.
                        $fieldValue['url'] = preg_replace('~^internal:/~', '', $fieldValue['uri']);
                        unset($fieldValue['uri']);
                        $valueList[$key] = json_encode($fieldValue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                        continue;
                    }

                    // Localize string fields.
                    if (!isset($fieldValue['format'])) {
                        $valueList[$key] = $value;
                        continue;
                    }
                    // If default mapping is not satisfactory, assign
                    // 'filtered_html' by default.
                    $fieldValue['format'] = isset($this->config['filters_mapping'][$fieldValue['format']]) ? $this->config['filters_mapping'][$fieldValue['format']] : 'filtered_html';
                    $valueList[$key] = json_encode($fieldValue, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                }
            }
            $data['value'][$langcode] = $valueList;
        }
    }

}
