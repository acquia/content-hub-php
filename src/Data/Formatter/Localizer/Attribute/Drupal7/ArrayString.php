<?php

namespace Acquia\ContentHubClient\Data\Formatter\Localizer\Attribute\Drupal7;

class ArrayString
{
    private $config = [
        'filters_mapping' => [
            'restricted_html' => 'filtered_html',
            'basic_html' => 'filtered_html',
            'full_html' => 'full_html',
            'rich_text' => 'full_html',
        ],
    ];

    public function localizeEntity(&$data)
    {
        foreach ($data['value'] as $langcode => $valueList) {
            foreach ($valueList as $key => $value) {
                $fieldValue = json_decode($value, TRUE);
                if (!is_array($fieldValue)) {
                    $valueList[$key] = $value;
                    continue;
                }

                // Localize Link fields - Hack.
                // @TODO: Fix this for Links Type.
                if (isset($fieldValue['uri'])) {
                    // Checking if it is an internal URL. If it is,
                    // then strip out the 'internal:/' prefix.
                    $fieldValue['url'] = (substr($fieldValue['uri'], 0, 10) === 'internal:/') ? str_replace('internal:/', '', $fieldValue['uri']) : $fieldValue['uri'];
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
            $data['value'][$langcode] = $valueList;
        }
    }

}
